import { Heading } from '@/common/heading/Heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { TeamBadge } from '@/modules/nfl-teams/components/TeamBadge';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import { RankingFormatSelect } from '@/modules/rankings/components/RankingFormatSelect';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { type BreadcrumbItem } from '@/types';
import { type DraftRanking, type RankingFormat } from '@/types/models';
import { PageProps } from '@inertiajs/core';
import { Head } from '@inertiajs/react';
import { rankItem } from '@tanstack/match-sorter-utils';
import {
  columnFilteringFeature,
  ColumnFiltersState,
  createColumnHelper,
  createFilteredRowModel,
  createSortedRowModel,
  flexRender,
  globalFilteringFeature,
  rowSortingFeature,
  sortFn_alphanumeric,
  sortFn_basic,
  SortingState,
  tableFeatures,
  useTable,
} from '@tanstack/react-table';
import { ArrowUpDown, Search } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Draft Rankings',
    href: '/rankings',
  },
];

interface DraftIndexProps extends PageProps {
  draftRankings: DraftRanking[];
  format: RankingFormat;
  formats: RankingFormat[];
}

function formatADV(adv: number | string | null) {
  if (!adv) {
    return 0;
  }

  if (typeof adv === 'string') {
    return Number(adv).toFixed(0);
  }

  return adv.toFixed(0);
}

// Fuzzy filter function for TanStack Table
const fuzzyFilter = (row: any, columnId: string, value: any, addMeta: any) => {
  // Rank the item
  const itemRank = rankItem(row.getValue(columnId), value);

  // Store the itemRank info
  addMeta({
    itemRank,
  });

  // Return if the item should be filtered in/out
  return itemRank.passed;
};

// Only the features this table uses are registered, so the rest tree shake out.
const features = tableFeatures({
  columnFilteringFeature,
  globalFilteringFeature,
  rowSortingFeature,
  filteredRowModel: createFilteredRowModel(),
  sortedRowModel: createSortedRowModel(),
  filterFns: { fuzzy: fuzzyFilter },
  sortFns: { alphanumeric: sortFn_alphanumeric, basic: sortFn_basic },
});

export default function Drafts({ draftRankings, format, formats }: DraftIndexProps) {
  const [sorting, setSorting] = useState<SortingState>([]);
  const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);
  const [globalFilter, setGlobalFilter] = useState('');
  let lastTier = 0;

  // Handle search input changes
  const handleSearchChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setGlobalFilter(e.target.value);
  };

  function getRowStyle(lastTier: number, tier: number | null) {
    return {
      borderTopWidth: lastTier > 0 && tier && tier !== lastTier ? 'thick' : 'inherit',
    };
  }

  const columnHelper = createColumnHelper<typeof features, DraftRanking>();

  const columns = columnHelper.columns([
    columnHelper.accessor((row) => row.player.full_name, {
      id: 'player',
      header: 'Player',
      cell: (info) => (
        <div className="grid w-full grid-cols-4 items-center gap-2">
          <div className="col-span-2">{info.getValue()}</div>
          <div className="col-span-1">
            <PositionBadge position={info.row.original.player.position} />
          </div>
          <div className="col-span-1">{info.row.original.player.team && <TeamBadge team={info.row.original.player.team} />}</div>
        </div>
      ),
    }),
    columnHelper.accessor('rank', {
      header: ({ column }) => {
        return (
          <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')} className="w-full justify-center">
            Rank
            <ArrowUpDown className="ml-2 h-4 w-4" />
          </Button>
        );
      },
      cell: (info) => <div className="text-center">{info.getValue()}</div>,
    }),
    columnHelper.accessor('tier', {
      header: ({ column }) => {
        return (
          <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')} className="w-full justify-center">
            Tier
            <ArrowUpDown className="ml-2 h-4 w-4" />
          </Button>
        );
      },
      cell: (info) => <div className="text-center">{info.getValue()}</div>,
    }),
    columnHelper.accessor('adp', {
      header: ({ column }) => {
        return (
          <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')} className="w-full justify-center">
            ADP
            <ArrowUpDown className="ml-2 h-4 w-4" />
          </Button>
        );
      },
      cell: (info) => <div className="text-center">{info.getValue()}</div>,
    }),
    columnHelper.accessor('adv', {
      header: ({ column }) => {
        return (
          <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')} className="w-full justify-center">
            ADV
            <ArrowUpDown className="ml-2 h-4 w-4" />
          </Button>
        );
      },
      cell: (info) => <div className="text-center">${formatADV(info.getValue()) || 0}</div>,
    }),
  ]);

  const table = useTable({
    features,
    data: draftRankings,
    columns,
    state: {
      sorting,
      columnFilters,
      globalFilter,
    },
    onSortingChange: setSorting,
    onColumnFiltersChange: setColumnFilters,
    onGlobalFilterChange: setGlobalFilter,
    globalFilterFn: 'fuzzy',
  });

  if (draftRankings.length === 0) {
    return (
      <AppLayout breadcrumbs={breadcrumbs}>
        <Head title="Draft Rankings" />

        <div className="flex-1 p-8">
          <div className="mb-6 flex flex-col items-start justify-between md:flex-row md:items-center">
            <Heading title="Draft Rankings" description="View Fantasy Player Rankings" />
            <RankingFormatSelect formats={formats} format={format} routeName="rankings.index" />
          </div>

          <div className="mb-8 rounded-lg border bg-card">
            <div className="border-b p-6 py-12 text-center">
              <h3 className="mb-2 text-lg font-medium">No {format.label} rankings have been imported yet.</h3>
              <p className="mb-6 text-gray-500 dark:text-gray-400">Import rankings using the artisan console.</p>
            </div>
          </div>
        </div>
      </AppLayout>
    );
  }

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Draft Rankings" />

      <div className="flex-1 p-8">
        <div className="mb-6 flex flex-col items-start justify-between md:flex-row md:items-center">
          <Heading title="Draft Rankings" description="View Fantasy Player Rankings" />
          <RankingFormatSelect formats={formats} format={format} routeName="rankings.index" />
        </div>

        <div className="grid w-full grid-cols-1">
          <Card className="flex max-h-[calc(100vh-12rem)] flex-col">
            <CardHeader className="py-0">
              <CardTitle>
                <div className="flex items-center justify-between">
                  <div>{format.label}</div>
                  <div className="relative max-w-sm">
                    <Search className="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                    <Input placeholder="Search players..." value={globalFilter ?? ''} onChange={handleSearchChange} className="max-w-sm pl-8" />
                  </div>
                </div>
              </CardTitle>
            </CardHeader>
            <CardContent className="flex-grow overflow-hidden py-0">
              <div className="relative">
                <Table containerClassName="max-h-[calc(100vh-22rem)] overflow-auto">
                  <TableHeader className="sticky top-0 z-10 bg-card shadow-sm [&_th]:bg-card">
                    {table.getHeaderGroups().map((headerGroup) => (
                      <TableRow key={headerGroup.id}>
                        {headerGroup.headers.map((header) => (
                          <TableHead key={header.id}>
                            {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                          </TableHead>
                        ))}
                      </TableRow>
                    ))}
                  </TableHeader>
                  <TableBody>
                    {table.getRowModel().rows.map((row) => {
                      const currentTier = row.original.tier;
                      const style = getRowStyle(lastTier, currentTier);
                      lastTier = currentTier !== null ? currentTier : lastTier;

                      return (
                        <TableRow key={row.id} style={style}>
                          {row.getAllCells().map((cell) => (
                            <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                          ))}
                        </TableRow>
                      );
                    })}
                  </TableBody>
                </Table>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}
