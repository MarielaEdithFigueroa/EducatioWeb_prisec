import type {
    ColumnDef,
    Row,
    SortingState,
} from '@tanstack/react-table';
import {
    flexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useReactTable,
} from '@tanstack/react-table';
import { CopyIcon, DownloadIcon, TableIcon } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import type { ComponentType, CSSProperties, ReactNode } from 'react';

import EureInput from './EureInput';

export interface EureDataTableAccion<T> {
    label: string;
    icon?: ComponentType<{ className?: string }>;
    onClick: (row: T) => void;
    color?: string;
    hidden?: (row: T) => boolean;
}

export interface EureDataTableProps<T> {
    columns: ColumnDef<T>[];
    data: T[];
    acciones?: EureDataTableAccion<T>[];
    className?: string;
    onVisibleRowsChange?: (rows: T[]) => void;
    showExportButtons?: boolean;
    showGlobalFilter?: boolean;
    globalFilterPlaceholder?: string;
    initialPageSize?: number;
    toolbarActions?: ReactNode;
    disablePagination?: boolean;
    stickyHeader?: boolean;
    getRowClassName?: (row: T) => string;
    getRowStyle?: (row: T) => CSSProperties | undefined;
    getColumnClassName?: (columnId: string, row?: T) => string;
}

const actionColorMap: Record<string, string> = {
    blue: 'text-primary hover:bg-primary/10',
    red: 'text-eure-error hover:bg-eure-error/10',
    green: 'text-eure-success hover:bg-eure-success/10',
    yellow: 'text-eure-warning hover:bg-eure-warning/10',
};

export default function EureDataTable<T>({
    columns,
    data,
    acciones = [],
    className,
    onVisibleRowsChange,
    showExportButtons = true,
    showGlobalFilter = true,
    globalFilterPlaceholder = 'Buscar...',
    initialPageSize = 10,
    toolbarActions,
    disablePagination = false,
    stickyHeader = false,
    getRowClassName,
    getRowStyle,
    getColumnClassName,
}: EureDataTableProps<T>) {
    const [globalFilter, setGlobalFilter] = useState('');
    const [sorting, setSorting] = useState<SortingState>([]);

    const columnasFinales = useMemo<ColumnDef<T>[]>(() => {
        if (!acciones.length) {
            return columns;
        }

        return [
            ...columns,
            {
                header: ' ',
                cell: ({ row }: { row: Row<T> }) => (
                    <div className="flex gap-2">
                        {acciones.filter(({ hidden }) => !hidden || !hidden(row.original)).map(({ label, icon: Icon, onClick, color = 'blue' }) => (
                            <button
                                key={label}
                                onClick={() => onClick(row.original)}
                                className={`cursor-pointer rounded p-1 ${actionColorMap[color] ?? actionColorMap.blue} transition`}
                                title={label}
                            >
                                {Icon ? (
                                    <Icon className="h-5 w-5" />
                                ) : (
                                    <span className="text-xs font-medium">{label}</span>
                                )}
                            </button>
                        ))}
                    </div>
                ),
            },
        ];
    }, [columns, acciones]);

    const table = useReactTable({
        data,
        columns: columnasFinales,
        state: {
            globalFilter,
            sorting,
        },
        initialState: {
            pagination: { pageSize: disablePagination ? Number.MAX_SAFE_INTEGER : initialPageSize },
        },
        onGlobalFilterChange: setGlobalFilter,
        onSortingChange: setSorting,
        getCoreRowModel: getCoreRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        getSortedRowModel: getSortedRowModel(),
    });

    const numericColumnIds = useMemo(() => {
        const rows = table.getCoreRowModel().rows;

        if (!rows.length) {
            return new Set<string>();
        }

        const firstRow = rows[0];

        return new Set(
            table
                .getAllLeafColumns()
                .filter((col) => typeof firstRow.getValue(col.id) === 'number')
                .map((col) => col.id),
        );
        // table is omitted intentionally — useReactTable returns a new reference on every render
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data]);

    const getDefaultAlign = (columnId: string): string =>
        numericColumnIds.has(columnId) ? 'text-right tabular-nums' : 'text-left';

    const getMetaClassName = (meta: unknown): string => {
        if (!meta || typeof meta !== 'object') {
            return '';
        }

        return (meta as { className?: string }).className ?? '';
    };

    const getColumnExportHeader = (
        col: ReturnType<typeof table.getAllLeafColumns>[number],
    ): string | undefined => {
        const exportHeader = (col.columnDef.meta as { exportHeader?: string } | undefined)
            ?.exportHeader;

        if (exportHeader !== undefined) {
            return exportHeader;
        }

        const header = col.columnDef.header;

        return typeof header === 'string' && header.trim() !== '' ? header : undefined;
    };

    const getExportableColumns = () =>
        table.getAllLeafColumns().filter((col) => getColumnExportHeader(col) !== undefined);

    const toText = (value: unknown): string => {
        if (value === null || value === undefined) {
            return '';
        }

        if (typeof value === 'object') {
            try {
                return JSON.stringify(value);
            } catch {
                return String(value);
            }
        }

        return String(value);
    };

    const buildMatrix = (): string[][] => {
        const exportableCols = getExportableColumns();
        const headers = exportableCols.map((col) => getColumnExportHeader(col) as string);
        const rows = table
            .getSortedRowModel()
            .rows.map((row) => exportableCols.map((col) => toText(row.getValue(col.id))));

        return [headers, ...rows];
    };

    const escapeCsvValue = (value: string): string => {
        if (
            value.includes('"') ||
            value.includes(';') ||
            value.includes('\n') ||
            value.includes('\r')
        ) {
            return `"${value.replace(/"/g, '""')}"`;
        }

        return value;
    };

    const downloadFile = (content: string, filename: string, type: string) => {
        const blob = new Blob([content], { type });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    };

    const handleExportCsv = () => {
        const csv = buildMatrix()
            .map((row) => row.map(escapeCsvValue).join(';'))
            .join('\r\n');
        downloadFile(`﻿${csv}`, 'tabla.csv', 'text/csv;charset=utf-8;');
    };

    const handleExportExcel = async () => {
        const XLSX = await import('xlsx');
        const matrix = buildMatrix();
        const ws = XLSX.utils.aoa_to_sheet(matrix);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Datos');
        XLSX.writeFile(wb, 'datos.xlsx');
    };

    const handleCopyToClipboard = async () => {
        const content = buildMatrix()
            .map((row) => row.join('\t'))
            .join('\n');
        await navigator.clipboard.writeText(content).catch(() => {});
    };

    const columnFilters = table.getState().columnFilters;
    const hasToolbar =
        showGlobalFilter || showExportButtons || Boolean(toolbarActions);

    useEffect(() => {
        if (!onVisibleRowsChange) {
            return;
        }

        const visibles = table.getFilteredRowModel().rows.map((r) => r.original);
        onVisibleRowsChange(visibles);
        // table is omitted intentionally — useReactTable returns a new reference on every render
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [columnFilters, globalFilter, onVisibleRowsChange]);

    const theadBg =
        'color-mix(in oklch, var(--eure-primary) 15%, var(--background))';

    return (
        <div className={className}>
            {/* Toolbar */}
            {hasToolbar && (
                <div className="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    {showGlobalFilter && (
                        <div className="w-full md:w-1/3">
                            <EureInput
                                type="text"
                                placeholder={globalFilterPlaceholder}
                                value={globalFilter}
                                onChange={(e) => setGlobalFilter(e.target.value)}
                            />
                        </div>
                    )}

                    {showExportButtons && (
                        <div className="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                className="flex items-center gap-1.5 rounded-lg border px-3.5 py-2 text-xs font-medium transition-all duration-150 border-[var(--eure-border)] text-[var(--eure-text)] bg-background hover:border-[var(--eure-accent)] hover:text-[var(--eure-accent)] hover:bg-[var(--eure-bg)]"
                                onClick={handleExportExcel}
                            >
                                <TableIcon className="h-4 w-4" />
                                XLSX
                            </button>
                            <button
                                type="button"
                                className="flex items-center gap-1.5 rounded-lg border px-3.5 py-2 text-xs font-medium transition-all duration-150 border-[var(--eure-border)] text-[var(--eure-text)] bg-background hover:border-[var(--eure-accent)] hover:text-[var(--eure-accent)] hover:bg-[var(--eure-bg)]"
                                onClick={handleExportCsv}
                            >
                                <DownloadIcon className="h-4 w-4" />
                                CSV (;)
                            </button>
                            <button
                                type="button"
                                className="flex items-center gap-1.5 rounded-lg border px-3.5 py-2 text-xs font-medium transition-all duration-150 border-[var(--eure-border)] text-[var(--eure-text)] bg-background hover:border-[var(--eure-accent)] hover:text-[var(--eure-accent)] hover:bg-[var(--eure-bg)]"
                                onClick={handleCopyToClipboard}
                            >
                                <CopyIcon className="h-4 w-4" />
                                Copiar
                            </button>
                        </div>
                    )}

                    {toolbarActions && (
                        <div className="ml-auto flex items-center gap-2">
                            {toolbarActions}
                        </div>
                    )}
                </div>
            )}

            {/* Table */}
            <div
                className={`overflow-x-auto rounded-lg shadow-md ${stickyHeader ? 'max-h-[70vh] overflow-y-auto' : 'overflow-y-clip'}`}
            >
                <table className="w-full bg-background text-sm" style={{ borderCollapse: 'collapse' }}>
                    <thead
                        className="text-xs uppercase tracking-wider"
                        style={{ color: 'var(--eure-text)', backgroundColor: theadBg }}
                    >
                        {table.getHeaderGroups().map((headerGroup) => (
                            <tr key={headerGroup.id}>
                                {headerGroup.headers.map((header) => (
                                    <th
                                        key={header.id}
                                        className={`p-3 font-bold border-b cursor-pointer select-none ${stickyHeader ? 'sticky top-0 z-10' : ''} ${getMetaClassName(header.column.columnDef.meta) || getDefaultAlign(header.column.id)} ${getColumnClassName?.(header.id) ?? ''}`}
                                        style={{
                                            borderColor: 'var(--eure-border)',
                                            backgroundColor: theadBg,
                                        }}
                                        onClick={header.column.getToggleSortingHandler()}
                                    >
                                        {flexRender(
                                            header.column.columnDef.header,
                                            header.getContext(),
                                        )}
                                        {
                                            {
                                                asc: ' 🔼',
                                                desc: ' 🔽',
                                            }[header.column.getIsSorted() as string] ?? null
                                        }
                                    </th>
                                ))}
                            </tr>
                        ))}
                    </thead>
                    <tbody>
                        {table.getRowModel().rows.map((row) => (
                            <tr
                                key={row.id}
                                className={`border-b transition-colors even:bg-eure-table-row-alt hover:bg-eure-table-row-hover ${getRowClassName?.(row.original) ?? ''}`}
                                style={{
                                    borderColor: 'var(--eure-border)',
                                    ...getRowStyle?.(row.original),
                                }}
                            >
                                {row.getVisibleCells().map((cell) => (
                                    <td
                                        key={cell.id}
                                        className={`p-3 ${getMetaClassName(cell.column.columnDef.meta) || getDefaultAlign(cell.column.id)} ${getColumnClassName?.(cell.column.id, row.original) ?? ''}`}
                                        style={{ color: 'var(--eure-text)' }}
                                    >
                                        {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Pagination */}
            {!disablePagination && (
                <div className="mt-4 flex items-center justify-between">
                    <div className="text-sm" style={{ color: 'var(--eure-text)' }}>
                        <label>Mostrar:</label>
                        <select
                            className="ml-2 rounded-lg border p-2 text-sm shadow-sm transition duration-200 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary/80"
                            style={{ borderColor: 'var(--eure-border)' }}
                            value={table.getState().pagination.pageSize}
                            onChange={(e) => {
                                table.setPageSize(Number(e.target.value));
                            }}
                        >
                            {[5, 10, 25, 31, 50, 100].map((pageSize) => (
                                <option key={pageSize} value={pageSize}>
                                    {pageSize}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div
                        className="flex items-center gap-2 text-sm"
                        style={{ color: 'var(--eure-text)' }}
                    >
                        <label htmlFor="selectorPagina">Ir a la página:</label>
                        <select
                            id="selectorPagina"
                            value={table.getState().pagination.pageIndex}
                            onChange={(e) => table.setPageIndex(Number(e.target.value))}
                            className="rounded-lg border p-2 text-sm shadow-sm transition duration-200 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary/80"
                            style={{ borderColor: 'var(--eure-border)' }}
                        >
                            {Array.from({ length: table.getPageCount() }, (_, i) => (
                                <option key={i} value={i}>
                                    {i + 1}
                                </option>
                            ))}
                        </select>

                        <span style={{ color: 'var(--eure-sub)' }}>de {table.getPageCount()}</span>
                    </div>

                    <div className="flex gap-2 text-sm">
                        <button
                            onClick={() => table.previousPage()}
                            disabled={!table.getCanPreviousPage()}
                            className="rounded-lg px-4 py-2 text-xs font-semibold tracking-wide text-white transition-all duration-150 disabled:cursor-not-allowed disabled:opacity-40"
                            style={{ backgroundColor: 'var(--eure-primary)' }}
                        >
                            &lt; Anterior
                        </button>

                        <button
                            onClick={() => table.nextPage()}
                            disabled={!table.getCanNextPage()}
                            className="rounded-lg px-4 py-2 text-xs font-semibold tracking-wide text-white transition-all duration-150 disabled:cursor-not-allowed disabled:opacity-40"
                            style={{ backgroundColor: 'var(--eure-primary)' }}
                        >
                            Siguiente &gt;
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
