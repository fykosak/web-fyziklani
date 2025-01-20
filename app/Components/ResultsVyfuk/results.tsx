import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import './style.scss';

interface Contestant {
    contestant: {
        contestantId: number; name: string; school: string;
    };
    rank: [number, number]; // in case of sharing a rank among multiple contestants, the first and last rank are stored
    submits: {
        [key: number]: number;
    };
    totalRank: [number, number];
    sum: number;
    category: number;
}

type Submits = Array<Contestant>;

interface Task {
    label: string;
    points: number;
    taskId: number;
}

type Tasks = {
    [series: number]: Array<Task>;
};

function Results({ data, series }: { data: { submits: { [key: string]: Submits; }, tasks: { [key: string]: Tasks; } }, series: number[] }) {
    const [selectedSeries, baseSetSelectedSeries] = useState(0);
    let tableManager: any = {};
    [tableManager.sortColum, tableManager.setSortColum] = useState(selectedSeries < 1 ? "sum" : "s" + selectedSeries);
    [tableManager.sortAsc, tableManager.setSortAsc] = useState(false);
    [tableManager.hideColum, tableManager.setHideColum] = useState({});
    const setSelectedSeries = (value: number) => {
        tableManager.setSortColum(value < 1 ? "sum" : "s" + value);
        baseSetSelectedSeries(value);
    }


    selectedSeries < 1 ? "sum" : "s" + selectedSeries

    const sortedCategories = Object.keys(data.submits).sort((a, b) => {
        return parseInt(b.slice(-1)) - parseInt(a.slice(-1));
    });
    const sortedSeries = series.sort((a, b) => a - b);

    // category selection
    let seriesSelection = sortedSeries.map((number) => {
        if (number < 7)
            return (
                <button
                    className={`btn me-2 ${selectedSeries == number ? 'btn-primary' : 'btn-outline-primary'}`}
                    onClick={() => setSelectedSeries(number)}
                >
                    {number}. série
                </button>
            )
        else
            return (
                <button
                    onClick={() => setSelectedSeries(number)}
                    className={`btn me-2 ${selectedSeries == number ? 'btn-primary' : 'btn-outline-primary'}`}
                >
                    {number - 7}. prázdninová série
                </button>
            )
    })
    seriesSelection.push(
        <button
            onClick={() => setSelectedSeries(0)}
            className={`btn me-2 ${selectedSeries == 0 ? 'btn-primary' : 'btn-outline-primary'}`}
        >
            Celkové výsledky
        </button>
    )
    let categoryContainers = [
        <div
            className='series-select my-2'
        >
            {seriesSelection}
        </div>]
    for (const category of sortedCategories) {
        let tableDef: any = React.useMemo(() => {
            let columns: ColumnDef[] = [];
            let taskLockup: { [key: number]: number } = {};
            columns.push({ colKey: "rank", label: "#", sortable: false, numerical: false });
            columns.push({ colKey: "name", label: "Jméno", sortable: false, numerical: false });
            columns.push({ colKey: "school", label: "Škola", sortable: false, numerical: false });
            for (let series in data.tasks[category]) {
                let tasks = data.tasks[category][series].sort((a, b) => {
                    return a.label.localeCompare(b.label);
                });
                for (let [key, t] of tasks.entries()) {
                    taskLockup[t.taskId] = columns.length;
                    columns.push({ colKey: "s" + series + "." + t.label, label: t.label + " (" + t.points + "\u00A0b)", sortable: true, numerical: true });
                }
                columns.push({ colKey: "s" + series, label: (parseInt(series) > 6 ? "P" + String(parseInt(series) - 7) : series), sortable: true, numerical: true });
            }
            columns.push({ colKey: "sum", label: "Celkem\nbodů", sortable: true, numerical: true });

            let outData = []
            for (let contestant of data.submits[category]) {
                let row: any = {};
                row["name"] = contestant.contestant.name;
                row["school"] = contestant.contestant.school;
                row["rank"] = contestant.rank[0] == contestant.rank[1] ? String(contestant.rank[0]) + "." : String(contestant.rank[0]) + ".–" + String(contestant.rank[1]) + ".";

                let totalSum = 0;
                for (let series in data.tasks[category]) {
                    let tasks = data.tasks[category][series].sort((a, b) => {
                        return a.label.localeCompare(b.label);
                    });
                    let seriesHasPoints = false;
                    let seriesSum = 0;
                    for (let [key, t] of tasks.entries()) {
                        if (contestant.submits.hasOwnProperty(t.taskId) && typeof contestant.submits[t.taskId] == 'number') {
                            row["s" + series + "." + t.label] = contestant.submits[t.taskId];
                            seriesSum += contestant.submits[t.taskId];
                            seriesHasPoints = true;
                        } else {
                            row["s" + series + "." + t.label] = '–';
                        }
                    }
                    row["s" + series] = seriesHasPoints ? seriesSum : "–";
                    totalSum += seriesSum;
                }
                row["sum"] = totalSum;
                outData.push(row);
            }
            return { columns: columns, data: outData };
        }, [data, series]);
        let hidden: string[] = []
        let keys: string[] = tableDef.columns.map((col: ColumnDef) => { return col.colKey });
        if (selectedSeries < 1) {
            hidden = keys.filter((v) => {
                if (v == "name" || v == "school" || v == "sum" || v == "rank") return false;
                return !/^s[0-9]$/.test(v);
            })
        } else {
            hidden = keys.filter((v) => {
                if (v == "name" || v == "school") return false;
                return !(new RegExp("s" + selectedSeries)).test(v);
            })
        }
        tableManager.hideColum = hidden.reduce((prev: any, c) => {
            prev[c] = true;
            return prev;
        }, {});

        tableDef.tableManager = tableManager;
        categoryContainers.push(
            <div className='mt-4'>
                <h2 className='mb-1'>Kategorie {parseInt(category.slice(-1))}. ročníků</h2>
                <SortTable tableDef={tableDef} />
            </div>
        );
    }


    return (<div>{categoryContainers}</div>)
}

type TableManager = {
    sortColum: string,
    setSortColum: React.Dispatch<React.SetStateAction<string>>,
    sortAsc: boolean,
    setSortAsc: React.Dispatch<React.SetStateAction<boolean>>,
    hideColum: { [key: string]: boolean }
    setHideColum: React.Dispatch<React.SetStateAction<any>>
};
type ColumnDef = { colKey: string, label: string | null, sortable: boolean, numerical: boolean };

type TableDef = { columns: Array<ColumnDef>, data: { [key: string]: any }[], tableManager: TableManager };

function SortColumn({ colKey, label = null, tableManager }: { colKey: string, label?: string | null, tableManager: TableManager }) {
    if (tableManager.hideColum.hasOwnProperty(colKey) && tableManager.hideColum[colKey] == true)
        return null;
    let element = tableManager.sortColum == colKey ? (
        <span style={{ color: 'black' }}>
            {tableManager.sortAsc ? <i className="fas fa-arrow-down-long"></i> : <i className="fas fa-arrow-up-long"></i>}
        </span>
    ) : (
        <span className="inactive-arrow">
            <i className="fas fa-arrows-up-down"></i>
        </span>
    );
    return (
        <th
            className={`clickable-header`}
            onClick={() => {
                if (colKey == tableManager.sortColum) {
                    tableManager.setSortAsc(!tableManager.sortAsc);
                } else {
                    tableManager.setSortColum(colKey);
                    tableManager.setSortAsc(false);
                }
            }}
        >
            {label == null ? colKey : label}&nbsp;
            {element}
        </th>
    )
}

function Column({ colKey, label = null, tableManager }: { colKey: string, label?: string | null, tableManager: TableManager }) {
    if (tableManager.hideColum.hasOwnProperty(colKey) && tableManager.hideColum[colKey] == true)
        return null;
    return (
        <th>
            {label == null ? colKey : label}
        </th>
    )
}

function SortTable({ tableDef }: { tableDef: TableDef }) {
    let [onlySome, setOnlySome] = useState(true);
    let tableHeader = tableDef.columns.map((columnDef) => {
        if (tableDef.tableManager.hideColum[columnDef.colKey])
            return null;
        return (columnDef.sortable ?
            <SortColumn colKey={columnDef.colKey} label={columnDef.label} tableManager={tableDef.tableManager}></SortColumn> :
            <Column colKey={columnDef.colKey} label={columnDef.label} tableManager={tableDef.tableManager}></Column>
        )
    })
    let data = tableDef.data;
    data.sort((a, b) => {
        let result: any = 0;
        if (
            tableDef.columns.reduce((returning, col) => {
                return returning || (col.colKey == tableDef.tableManager.sortColum && col.numerical)
            }, false)
        ) {
            result = ((typeof a[tableDef.tableManager.sortColum] == "number" ? a[tableDef.tableManager.sortColum] : 0)
                - (typeof b[tableDef.tableManager.sortColum] == "number" ? b[tableDef.tableManager.sortColum] : 0));
        } else {
            result = String(a[tableDef.tableManager.sortColum]).localeCompare(b[tableDef.tableManager.sortColum]);
        }
        if (!tableDef.tableManager.sortAsc) {
            result = -result;
        }
        return result;
    })
    if (onlySome) {
        data = data.slice(0, 25);
    }
    let tableBody: JSX.Element[] = [];
    for (let dat of data) {
        let dataRow: JSX.Element[] = [];
        let show = false;
        for (let c of tableDef.columns) {
            if (!tableDef.tableManager.hideColum.hasOwnProperty(c.colKey) || !tableDef.tableManager.hideColum[c.colKey]) {
                show ||= typeof dat[c.colKey] == 'number'
                dataRow.push(<td>{dat[c.colKey]}</td>)
            }
        }
        if (show) {
            tableBody.push(<tr>{dataRow}</tr>)
        };
    }


    return (<>
        <table className='table table-hover contest-results table-sm mb-0'>
            <thead><tr>
                {tableHeader}
            </tr></thead>
            <tbody>{tableBody}</tbody>
        </table>
        <button
            className="btn btn-primary button-collapse-header my-2"
            type="button"
            onClick={() => setOnlySome(!onlySome)}
        >
            {onlySome ? 'Zobrazit všechny' : 'Zobrazit méně'}
        </button>
    </>);
}

document.addEventListener('DOMContentLoaded', () => {
    const element = document.getElementById('contest-results');
    if (element) {
        const data = JSON.parse(element.getAttribute('data-data'));
        const series = JSON.parse(element.getAttribute('data-series'));
        ReactDOM.render(
            <Results data={data} series={series} />,
            element
        );
    }
});
