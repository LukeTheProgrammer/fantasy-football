import { AppLayout } from '@/pages/layouts/AppLayout';
import { Heading } from '@/common/heading/Heading';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import { type Player, type Team, type Position } from '@/types/models';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { MultiSelect } from '@/common/multi-select/MultiSelect';
import { FormDialog } from '@/common/form-dialog/FormDialog';
import { PlayerForm, type PlayerFormData } from '@/modules/players/components/PlayerForm';
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { useState, useMemo, useEffect } from 'react';
import axios from 'axios';
import { PlayerAliases } from '@/modules/players/components/PlayerAliases';
import { MissingPlayers } from '@/modules/players/components/MissingPlayers';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'NFL Players',
    href: '/players',
  },
];

interface NflPlayersProps extends PageProps {
  players: Player[];
  teams: Team[];
  positions: Position[];
}

export default function NflPlayers({ players, teams, positions }: NflPlayersProps) {
  const [selectedTeams, setSelectedTeams] = useState<string[]>([]);
  const [selectedPositions, setSelectedPositions] = useState<string[]>([]);
  const [searchQuery, setSearchQuery] = useState<string>('');
  const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
  const [selectedPlayer, setSelectedPlayer] = useState<Player | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const [playersData, setPlayersData] = useState<Player[]>(players);
  const [currentPage, setCurrentPage] = useState(1);
  const [playersPerPage] = useState(10);
  const [editForm, setEditForm] = useState<PlayerFormData>({
    first_name: '',
    last_name: '',
    full_name: '',
    position_id: '',
    team_id: '',
    height: '',
    weight: '',
    college: '',
    draft_year: '',
    jersey_number: '',
    aliases: [],
  });

  const positionOrder = useMemo(() => [
    'QB', 'RB', 'WR', 'TE', 'DST', 'K',
    'OT', 'G', 'C', 'DT', 'DE', 'LB', 'CB', 'S',
    'FB', 'LS', 'P'
  ], []);

  const handleSavePlayer = async () => {
    if (!selectedPlayer) return;

    setIsSaving(true);
    try {
      const response = await axios.put(`/api/players/${selectedPlayer.id}`, editForm);
      const updatedPlayer = response.data;

      // Update the players data in state
      setPlayersData(prevPlayers =>
        prevPlayers.map(player =>
          player.id === updatedPlayer.id ? updatedPlayer : player
        )
      );

      setIsEditDialogOpen(false);
    } catch (error) {
      console.error('Error updating player:', error);
      // TODO: Add proper error handling/toast notification
      alert('Failed to update player. Please try again.');
    } finally {
      setIsSaving(false);
    }
  };

  const filteredPlayers = useMemo(() => {
    let filtered = playersData;

    // Filter by search query
    if (searchQuery.trim()) {
      const query = searchQuery.toLowerCase().trim();
      filtered = filtered.filter(player =>
        player.full_name.toLowerCase().includes(query) ||
        player.first_name.toLowerCase().includes(query) ||
        player.last_name.toLowerCase().includes(query)
      );
    }

    // Filter by teams
    if (selectedTeams.length > 0) {
      filtered = filtered.filter(player => {
        if (selectedTeams.includes('fa')) {
          return !player.team || selectedTeams.includes(player.team.id);
        }
        return player.team && selectedTeams.includes(player.team.id);
      });
    }

    // Filter by positions
    if (selectedPositions.length > 0) {
      filtered = filtered.filter(player =>
        player.position && selectedPositions.includes(player.position.id)
      );
    }

    // Sort by position order

    filtered.sort((a, b) => {
      const aIndex = positionOrder.indexOf(a.position?.abbreviation || '');
      const bIndex = positionOrder.indexOf(b.position?.abbreviation || '');

      // If both positions are in the order, sort by position order
      if (aIndex !== -1 && bIndex !== -1) {
        return aIndex - bIndex;
      }

      // If only one position is in the order, prioritize it
      if (aIndex !== -1) return -1;
      if (bIndex !== -1) return 1;

      // If neither position is in the order, sort alphabetically by name
      return a.full_name.localeCompare(b.full_name);
    });

    return filtered;
  }, [playersData, selectedTeams, selectedPositions, searchQuery, positionOrder]);

  // Pagination calculations
  const totalPlayers = filteredPlayers.length;
  const totalPages = Math.ceil(totalPlayers / playersPerPage);
  const startIndex = (currentPage - 1) * playersPerPage;
  const endIndex = startIndex + playersPerPage;
  const currentPlayers = filteredPlayers.slice(startIndex, endIndex);

  // Reset to page 1 when filters change
  const resetPagination = () => {
    setCurrentPage(1);
  };

  // Prepare options for MultiSelect components
  const teamOptions = [
    { label: 'Free Agents', value: 'fa' },
    ...teams.map(team => ({
      label: `${team.location} ${team.name}`,
      value: team.id
    }))
  ];

  const positionOptions = positions
    .sort((a, b) => {
      const aIndex = positionOrder.indexOf(a.abbreviation);
      const bIndex = positionOrder.indexOf(b.abbreviation);
      // If position not found in order, put it at the end
      if (aIndex === -1 && bIndex === -1) return a.abbreviation.localeCompare(b.abbreviation);
      if (aIndex === -1) return 1;
      if (bIndex === -1) return -1;
      return aIndex - bIndex;
    })
    .map(position => ({
      label: `${position.name} (${position.abbreviation})`,
      value: position.id
    }));

  // Reset pagination when filters change
  useEffect(() => {
    resetPagination();
  }, [selectedTeams, selectedPositions, searchQuery]);
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="NFL Players" />

      <div className="flex-1 p-8">
        <Heading
          title="NFL Players"
          description="View and manage NFL players"
        />

        <div className="mb-8">
          <Tabs defaultValue="players">
            <div className="flex items-center justify-between mb-6">
              <div className="flex items-center">
                <TabsList>
                  <TabsTrigger className="w-[7rem]" value="players">Players</TabsTrigger>
                  <TabsTrigger className="w-[7rem]" value="aliases">Aliases</TabsTrigger>
                  <TabsTrigger className="w-[7rem]" value="missing">Missing</TabsTrigger>
                </TabsList>
              </div>
            </div>

            <TabsContent value="players">
              <div className="flex justify-between items-center gap-6 mb-6">
                <div className="flex justify-start items-center grow-1 gap-6">
                  <div className="flex items-center gap-4">
                    <MultiSelect
                      options={teamOptions}
                      onValueChange={setSelectedTeams}
                      defaultValue={selectedTeams}
                      placeholder="Select teams..."
                      className="w-[250px]"
                      maxCount={3}
                    />
                  </div>
                  <div className="flex items-center gap-4">
                    <MultiSelect
                      options={positionOptions}
                      onValueChange={setSelectedPositions}
                      defaultValue={selectedPositions}
                      placeholder="Select positions..."
                      className="w-[200px]"
                      maxCount={3}
                    />
                  </div>
                </div>

                <div className="flex justify-end items-center gap-6">
                  <Input
                    type="search"
                    placeholder="Search players"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    className="max-w-sm"
                  />
                </div>
              </div>

              <div className="rounded-lg border bg-card p-6">
          {currentPlayers.length === 0 ? (
            <div className="py-12 text-center">
              <h3 className="mb-2 text-lg font-medium">No players found</h3>
              <p className="text-gray-500 dark:text-gray-400">
                {selectedTeams.length === 0 && selectedPositions.length === 0 && !searchQuery.trim()
                  ? 'There are currently no NFL players in the database.'
                  : 'No players found for the selected filters.'}
              </p>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead className="w-16">Photo</TableHead>
                  <TableHead>Name</TableHead>
                  <TableHead>Position</TableHead>
                  <TableHead>Team</TableHead>
                  <TableHead>Jersey #</TableHead>
                  <TableHead>Height</TableHead>
                  <TableHead>Weight</TableHead>
                  <TableHead>College</TableHead>
                  <TableHead>Draft Year</TableHead>
                  <TableHead className="w-20">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {currentPlayers.map((player) => (
                  <TableRow key={player.id}>
                    <TableCell className="w-16">
                      {player.headshot ? (
                        <img
                          src={player.headshot}
                          alt={`${player.full_name} headshot`}
                          className="w-12 h-12 rounded-full object-cover"
                          onError={(e) => {
                            e.currentTarget.style.display = 'none';
                          }}
                        />
                      ) : (
                        <div className="w-12 h-12 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                          <span className="text-xs font-medium text-gray-500 dark:text-gray-400">
                            {player.full_name.split(' ').map(n => n[0]).join('').slice(0, 2)}
                          </span>
                        </div>
                      )}
                    </TableCell>
                    <TableCell className="font-medium">{player.full_name}</TableCell>
                    <TableCell>{player.position?.abbreviation || '--'}</TableCell>
                    <TableCell>{player.team?.abbreviation || 'FA'}</TableCell>
                    <TableCell>{player.jersey_number || ''}</TableCell>
                    <TableCell>{player.height || '--'}</TableCell>
                    <TableCell>{player.weight ? `${player.weight} lbs` : '--'}</TableCell>
                    <TableCell>{player.college || '--'}</TableCell>
                    <TableCell>{player.draft_year || '--'}</TableCell>
                    <TableCell className="w-20">
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => {
                          setSelectedPlayer(player);
                          setEditForm({
                            first_name: player.first_name || '',
                            last_name: player.last_name || '',
                            full_name: player.full_name || '',
                            position_id: player.position_id || '',
                            team_id: player.team_id || '',
                            height: player.height || '',
                            weight: player.weight || '',
                            college: player.college || '',
                            draft_year: player.draft_year || '',
                            jersey_number: player.jersey_number || '',
                            aliases: player.aliases?.map(alias => ({ name: alias.name || alias.alias })) || [],
                          });
                          setIsEditDialogOpen(true);
                        }}
                      >
                        Edit
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}

          {/* Pagination Controls */}
          {totalPlayers > playersPerPage && (
            <div className="flex items-center justify-between mt-6">
              <div className="text-sm text-gray-500 dark:text-gray-400">
                Showing {startIndex + 1} to {Math.min(endIndex, totalPlayers)} of {totalPlayers} players
              </div>

              <div className="flex items-center gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                  disabled={currentPage === 1}
                >
                  Previous
                </Button>

                <div className="flex items-center gap-1">
                  {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
                    let pageNumber;
                    if (totalPages <= 5) {
                      pageNumber = i + 1;
                    } else if (currentPage <= 3) {
                      pageNumber = i + 1;
                    } else if (currentPage >= totalPages - 2) {
                      pageNumber = totalPages - 4 + i;
                    } else {
                      pageNumber = currentPage - 2 + i;
                    }

                    return (
                      <Button
                        key={pageNumber}
                        variant={currentPage === pageNumber ? "default" : "outline"}
                        size="sm"
                        onClick={() => setCurrentPage(pageNumber)}
                        className="w-8 h-8 p-0"
                      >
                        {pageNumber}
                      </Button>
                    );
                  })}
                </div>

                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
                  disabled={currentPage === totalPages}
                >
                  Next
                </Button>
              </div>
            </div>
          )}
              </div>
            </TabsContent>

            <TabsContent value="aliases">
              <PlayerAliases />
            </TabsContent>

            <TabsContent value="missing">
              <MissingPlayers />
            </TabsContent>
          </Tabs>
        </div>

        {/* Edit Player Dialog */}
        <FormDialog
          open={isEditDialogOpen}
          onOpenChange={setIsEditDialogOpen}
          title={`Edit Player: ${selectedPlayer?.full_name || ''}`}
          description="Update the player's information below."
          onSave={handleSavePlayer}
          isLoading={isSaving}
        >
          <PlayerForm
            formData={editForm}
            onChange={setEditForm}
          />
        </FormDialog>
      </div>
    </AppLayout>
  );
}
