import { type PlayerMissing, type Player } from '@/types/models';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import PlayerForm, { type PlayerFormData } from '@/forms/player-form';
import { useState, useMemo, useEffect } from 'react';
import axios from 'axios';

export default function MissingPlayers() {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedPlayer, setSelectedPlayer] = useState<PlayerMissing | null>(null);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [actionType, setActionType] = useState<'create' | 'alias' | 'ignore' | null>(null);
  const [selectedExistingPlayer, setSelectedExistingPlayer] = useState<string>('');
  const [existingPlayers, setExistingPlayers] = useState<Player[]>([]);
  const [searchExistingPlayer, setSearchExistingPlayer] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [missingPlayersData, setMissingPlayersData] = useState<PlayerMissing[]>([]);
  const [isLoadingData, setIsLoadingData] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);
  const [playersPerPage] = useState(10);
  const [createPlayerForm, setCreatePlayerForm] = useState<PlayerFormData>({
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

  // Fetch missing players data
  useEffect(() => {
    const fetchMissingPlayers = async () => {
      setIsLoadingData(true);
      try {
        const response = await axios.get('/api/players-missing');
        setMissingPlayersData(response.data);
      } catch (error) {
        console.error('Error fetching missing players:', error);
        setMissingPlayersData([]);
      } finally {
        setIsLoadingData(false);
      }
    };

    fetchMissingPlayers();
  }, []);


  const filteredPlayers = useMemo(() => {
    if (!searchTerm) return missingPlayersData;

    return missingPlayersData.filter(player => {
      const sourceData = typeof player.source_data === 'string'
        ? JSON.parse(player.source_data)
        : player.source_data;

      const playerName = sourceData?.player_name || '';
      const sourceClass = player.source_class || '';

      return playerName.toLowerCase().includes(searchTerm.toLowerCase()) ||
             sourceClass.toLowerCase().includes(searchTerm.toLowerCase());
    });
  }, [missingPlayersData, searchTerm]);

  // Pagination calculations
  const totalPlayers = filteredPlayers.length;
  const totalPages = Math.ceil(totalPlayers / playersPerPage);
  const startIndex = (currentPage - 1) * playersPerPage;
  const endIndex = startIndex + playersPerPage;
  const currentPlayers = filteredPlayers.slice(startIndex, endIndex);

  const trunc = (val: any, len: number = 10) => {
    let str = JSON.stringify(val);

    if (str.startsWith('"')) {
      str = str.slice(1);
    }

    if (str.endsWith('"')) {
      str = str.slice(0, -1);
    }

    str = str.replace(/\\"/g, '"');

    return str.length > len ? str.substring(0, len) + '...' : str;
  };

  // Reset to page 1 when filters change
  useEffect(() => {
    setCurrentPage(1);
  }, [searchTerm]);

  const handlePlayerClick = (player: PlayerMissing) => {
    setSelectedPlayer(player);
    setDialogOpen(true);
    setActionType(null);
    setSelectedExistingPlayer('');
    setSearchExistingPlayer('');

    // Initialize form data from source data
    const sourceData = getSourceData(player.source_data);
    const fullName = sourceData.player_name || sourceData.full_name || '';
    const nameParts = fullName.split(' ');
    const firstName = nameParts[0] || '';
    const lastName = nameParts.slice(1).join(' ') || '';

    setCreatePlayerForm({
      first_name: firstName,
      last_name: lastName,
      full_name: fullName,
      position_id: sourceData.player_position_id || '',
      team_id: sourceData.player_team_id || '',
      height: sourceData.height || '',
      weight: sourceData.weight || '',
      college: sourceData.college || '',
      draft_year: sourceData.draft_year || '',
      jersey_number: sourceData.jersey_number || '',
      aliases: [],
    });
  };

  const searchForExistingPlayers = async (searchTerm: string) => {
    if (searchTerm.length < 2) {
      setExistingPlayers([]);
      return;
    }

    try {
      const response = await axios.post('/api/players/search', { search: searchTerm });
      setExistingPlayers(response.data);
    } catch (error) {
      console.error('Error searching players:', error);
      setExistingPlayers([]);
    }
  };

  useEffect(() => {
    if (searchExistingPlayer) {
      const timeoutId = setTimeout(() => {
        searchForExistingPlayers(searchExistingPlayer);
      }, 300);
      return () => clearTimeout(timeoutId);
    } else {
      setExistingPlayers([]);
    }
  }, [searchExistingPlayer]);

  const handleCreatePlayer = async () => {
    if (!selectedPlayer) return;

    setIsLoading(true);
    try {
      const sourceData = getSourceData(selectedPlayer.source_data);

      const playerData = {
        ...createPlayerForm,
        position_id: sourceData.player_position_id || 'RB', // Default fallback
        team_id: sourceData.player_team_id || 'FA', // Default to Free Agent
        fp_id: sourceData.player_id ? String(sourceData.player_id) : undefined,
      };

      await axios.post('/api/players', playerData);
      await axios.delete(`/api/players-missing/${selectedPlayer.id}`);

      // Remove the player from local state
      setMissingPlayersData(prevPlayers =>
        prevPlayers.filter(player => player.id !== selectedPlayer.id)
      );

      setDialogOpen(false);
    } catch (error) {
      console.error('Error creating player:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleCreateAlias = async () => {
    if (!selectedPlayer || !selectedExistingPlayer) return;

    setIsLoading(true);
    try {
      const sourceData = getSourceData(selectedPlayer.source_data);

      const aliasData = {
        player_id: selectedExistingPlayer,
        name: sourceData.player_name || '',
      };

      await axios.post('/api/player-aliases', aliasData);
      await axios.delete(`/api/players-missing/${selectedPlayer.id}`);

      // Remove the player from local state
      setMissingPlayersData(prevPlayers =>
        prevPlayers.filter(player => player.id !== selectedPlayer.id)
      );

      setDialogOpen(false);
    } catch (error) {
      console.error('Error creating alias:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleIgnore = async () => {
    if (!selectedPlayer) return;

    setIsLoading(true);
    try {
      await axios.delete(`/api/players-missing/${selectedPlayer.id}`);

      // Remove the player from local state
      setMissingPlayersData(prevPlayers =>
        prevPlayers.filter(player => player.id !== selectedPlayer.id)
      );

      setDialogOpen(false);
    } catch (error) {
      console.error('Error deleting player not found record:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleAction = () => {
    switch (actionType) {
      case 'create':
        return handleCreatePlayer();
      case 'alias':
        return handleCreateAlias();
      case 'ignore':
        return handleIgnore();
      default:
        return;
    }
  };

  const getSourceData = (sourceData: string | object) => {
    if (typeof sourceData === 'string') {
      try {
        return JSON.parse(sourceData);
      } catch {
        return {};
      }
    }
    return sourceData || {};
  };

  return (
    <div className="rounded-lg border bg-card p-6">
      <div className="flex justify-between items-center gap-6 mb-6">
        <h2>Missing Players</h2>
        <Input
          type="search"
          placeholder="Search by player name or source class..."
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          className="max-w-sm"
        />
      </div>

      <div className="mb-8 rounded-lg border bg-card p-6">
        {isLoadingData ? (
          <div className="py-12 text-center">
            <h3 className="mb-2 text-lg font-medium">Loading missing players...</h3>
            <p className="text-gray-500 dark:text-gray-400">
              Please wait while we fetch the missing players data.
            </p>
          </div>
        ) : currentPlayers.length === 0 ? (
            <div className="py-12 text-center">
              <h3 className="mb-2 text-lg font-medium">
                {searchTerm ? 'No matching players found' : 'No players not found'}
              </h3>
              <p className="text-gray-500 dark:text-gray-400">
                {searchTerm
                  ? 'Try adjusting your search terms.'
                  : 'All players have been successfully matched.'}
              </p>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Source</TableHead>
                  <TableHead>Data</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {currentPlayers.map((playerMissing) => {
                  const sourceData = getSourceData(playerMissing.source_data);
                  const sourceJson = JSON.stringify(sourceData);
                  const source = sourceJson.slice(0, 150) + (sourceJson.length > 150 ? '...' : '');

                  return (
                    <TableRow key={playerMissing.id}>
                      <TableCell className="text-sm text-muted-foreground">
                        {playerMissing.source_class?.split('\\').pop() || playerMissing.source_class}
                      </TableCell>
                      <TableCell>{source}</TableCell>
                      <TableCell>
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handlePlayerClick(playerMissing)}
                        >
                          Handle
                        </Button>
                      </TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          )}

          {/* Pagination Controls */}
          {totalPlayers > playersPerPage && (
            <div className="flex items-center justify-between mt-6">
              <div className="text-sm text-gray-500 dark:text-gray-400">
                Showing {startIndex + 1} to {Math.min(endIndex, totalPlayers)} of {totalPlayers} missing players
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

        {/* Handle Player Dialog */}
        <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
          <DialogContent className="max-w-2xl max-h-[90vh] flex flex-col">
            <DialogHeader>
              <DialogTitle>Handle Player Not Found</DialogTitle>
            </DialogHeader>

            <div className="flex-1 overflow-y-auto max-h-[60vh] pr-2">
              {selectedPlayer && (
                <div className="space-y-6">
                {/* Player Info */}
                <div className="rounded-lg border p-4 bg-muted/50">
                  <h4 className="font-medium mb-2">Player Information</h4>
                  <div className="gap-2 text-sm max-h-[10vh] overflow-y-auto">
                    {Object.entries(selectedPlayer.source_data).map(([key, value]) => (
                      <div key={key} className="flex justify-start items-center gap-2">
                        <p className="text-muted-foreground w-[5rem]">{key}:</p>
                        <p className="font-medium">{trunc(value, 30)}</p>
                      </div>
                    ))}
                  </div>
                </div>

                {/* Action Selection */}
                <div className="space-y-4">
                  <Label className="text-base font-medium">Choose an action:</Label>

                  <Tabs value={actionType || ''} onValueChange={(value) => setActionType(value as 'create' | 'alias' | 'ignore')}>
                    <TabsList className="grid w-full grid-cols-3">
                      <TabsTrigger value="create">Create Player</TabsTrigger>
                      <TabsTrigger value="alias">Create Alias</TabsTrigger>
                      <TabsTrigger value="ignore">Ignore</TabsTrigger>
                    </TabsList>

                    <TabsContent value="create" className="space-y-3">
                      <PlayerForm
                        formData={createPlayerForm}
                        onChange={setCreatePlayerForm}
                        config={{ showOptionalFields: false }}
                      />
                    </TabsContent>

                    <TabsContent value="alias" className="space-y-3">
                      <div className="p-4 bg-muted/50 rounded-lg">
                        <p className="text-sm text-muted-foreground">
                          This will create an alias linking the import data to an existing player.
                        </p>
                      </div>

                      <div className="space-y-3">
                        <Label htmlFor="search-player">Search for existing player:</Label>
                        <Input
                          id="search-player"
                          placeholder="Type player name to search..."
                          value={searchExistingPlayer}
                          onChange={(e) => setSearchExistingPlayer(e.target.value)}
                        />

                        {existingPlayers.length > 0 && (
                          <Select value={selectedExistingPlayer} onValueChange={setSelectedExistingPlayer}>
                            <SelectTrigger>
                              <SelectValue placeholder="Select a player" />
                            </SelectTrigger>
                            <SelectContent>
                              {existingPlayers.map((player) => (
                                <SelectItem key={player.id} value={player.id.toString()}>
                                  {player.full_name} - {player.position?.abbreviation} ({player.team?.abbreviation || 'FA'})
                                </SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                        )}
                      </div>
                    </TabsContent>

                    <TabsContent value="ignore" className="space-y-3">
                      <div className="p-4 bg-muted/50 rounded-lg">
                        <p className="text-sm text-muted-foreground">
                          This will permanently delete this missing player record without taking any action.
                        </p>
                      </div>
                    </TabsContent>
                  </Tabs>
                </div>
              </div>
            )}
            </div>

            <DialogFooter>
              <Button variant="outline" onClick={() => setDialogOpen(false)} disabled={isLoading}>
                Cancel
              </Button>
              <Button
                onClick={handleAction}
                disabled={!actionType || (actionType === 'alias' && !selectedExistingPlayer) || isLoading}
              >
                {isLoading ? 'Processing...' : 'Execute'}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
    </div>
  );
}
