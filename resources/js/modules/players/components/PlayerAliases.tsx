import { FormDialog } from '@/common/form-dialog/FormDialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { AliasForm, type AliasFormData } from '@/modules/players/components/AliasForm';
import { type PlayerAlias } from '@/types/models';
import axios from 'axios';
import { useEffect, useMemo, useState } from 'react';

export function PlayerAliases() {
  const [searchQuery, setSearchQuery] = useState<string>('');
  const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
  const [selectedAlias, setSelectedAlias] = useState<PlayerAlias | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const [aliasesData, setAliasesData] = useState<PlayerAlias[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);
  const [aliasesPerPage] = useState(10);
  const [editForm, setEditForm] = useState<AliasFormData>({
    name: '',
    player_id: '',
  });

  const handleSaveAlias = async () => {
    if (!selectedAlias) return;

    setIsSaving(true);
    try {
      const response = await axios.put(`/api/player-aliases/${selectedAlias.id}`, editForm);
      const updatedAlias = response.data;

      // Update the aliases data in state
      setAliasesData((prevAliases) => prevAliases.map((alias) => (alias.id === updatedAlias.id ? updatedAlias : alias)));

      setIsEditDialogOpen(false);
    } catch (error) {
      console.error('Error updating alias:', error);
      // TODO: Add proper error handling/toast notification
      alert('Failed to update alias. Please try again.');
    } finally {
      setIsSaving(false);
    }
  };

  const filteredAliases = useMemo(() => {
    let filtered = aliasesData;

    // Filter by search query
    if (searchQuery.trim()) {
      const query = searchQuery.toLowerCase().trim();
      filtered = filtered.filter(
        (alias) =>
          alias.name.toLowerCase().includes(query) ||
          alias.player?.full_name.toLowerCase().includes(query) ||
          alias.player?.position?.name.toLowerCase().includes(query) ||
          alias.player?.team?.name.toLowerCase().includes(query),
      );
    }

    // Sort alphabetically by alias name
    filtered.sort((a, b) => a.name.localeCompare(b.name));

    return filtered;
  }, [aliasesData, searchQuery]);

  // Pagination calculations
  const totalAliases = filteredAliases.length;
  const totalPages = Math.ceil(totalAliases / aliasesPerPage);
  const startIndex = (currentPage - 1) * aliasesPerPage;
  const endIndex = startIndex + aliasesPerPage;
  const currentAliases = filteredAliases.slice(startIndex, endIndex);

  // Reset to page 1 when filters change
  const resetPagination = () => {
    setCurrentPage(1);
  };

  // Fetch aliases data on component mount
  useEffect(() => {
    const fetchAliases = async () => {
      try {
        setIsLoading(true);
        const response = await axios.get('/api/player-aliases');
        setAliasesData(response.data);
      } catch (error) {
        console.error('Error fetching aliases:', error);
        // TODO: Add proper error handling/toast notification
      } finally {
        setIsLoading(false);
      }
    };

    fetchAliases();
  }, []);

  // Reset pagination when search changes
  useEffect(() => {
    resetPagination();
  }, [searchQuery]);
  return (
    <div className="rounded-lg border bg-card p-6">
      <div className="mb-6 flex items-center justify-between gap-6">
        <h2>Player Aliases</h2>
        <Input
          type="search"
          placeholder="Search aliases or players..."
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          className="max-w-sm"
        />
      </div>

      <div className="mb-8 rounded-lg border bg-card p-6">
        {isLoading ? (
          <div className="py-12 text-center">
            <h3 className="mb-2 text-lg font-medium">Loading aliases...</h3>
            <p className="text-gray-500 dark:text-gray-400">Please wait while we fetch the player aliases.</p>
          </div>
        ) : currentAliases.length === 0 ? (
          <div className="py-12 text-center">
            <h3 className="mb-2 text-lg font-medium">No aliases found</h3>
            <p className="text-gray-500 dark:text-gray-400">
              {!searchQuery.trim() ? 'There are currently no player aliases in the database.' : 'No aliases found for the search query.'}
            </p>
          </div>
        ) : (
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Alias Name</TableHead>
                <TableHead>Player Name</TableHead>
                <TableHead>Position</TableHead>
                <TableHead>Team</TableHead>
                <TableHead className="w-20">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {currentAliases.map((alias) => (
                <TableRow key={alias.id}>
                  <TableCell className="font-medium">{alias.name}</TableCell>
                  <TableCell>{alias.player?.full_name || '--'}</TableCell>
                  <TableCell>{alias.player?.position_id || '--'}</TableCell>
                  <TableCell>{alias.player?.team_id || 'FA'}</TableCell>
                  <TableCell className="w-20">
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => {
                        setSelectedAlias(alias);
                        setEditForm({
                          name: alias.name || '',
                          player_id: alias.player_id?.toString() || '',
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
        {totalAliases > aliasesPerPage && (
          <div className="mt-6 flex items-center justify-between">
            <div className="text-sm text-gray-500 dark:text-gray-400">
              Showing {startIndex + 1} to {Math.min(endIndex, totalAliases)} of {totalAliases} aliases
            </div>

            <div className="flex items-center gap-2">
              <Button variant="outline" size="sm" onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 1))} disabled={currentPage === 1}>
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
                      variant={currentPage === pageNumber ? 'default' : 'outline'}
                      size="sm"
                      onClick={() => setCurrentPage(pageNumber)}
                      className="h-8 w-8 p-0"
                    >
                      {pageNumber}
                    </Button>
                  );
                })}
              </div>

              <Button
                variant="outline"
                size="sm"
                onClick={() => setCurrentPage((prev) => Math.min(prev + 1, totalPages))}
                disabled={currentPage === totalPages}
              >
                Next
              </Button>
            </div>
          </div>
        )}
      </div>

      {/* Edit Alias Dialog */}
      <FormDialog
        open={isEditDialogOpen}
        onOpenChange={setIsEditDialogOpen}
        title={`Edit Alias: ${selectedAlias?.name || ''}`}
        description="Update the alias name below."
        onSave={handleSaveAlias}
        isLoading={isSaving}
      >
        <AliasForm
          formData={editForm}
          onChange={setEditForm}
          playerInfo={
            selectedAlias?.player
              ? {
                  full_name: selectedAlias.player.full_name,
                  position: selectedAlias.player.position_id,
                  team: selectedAlias.player.team_id,
                }
              : undefined
          }
        />
      </FormDialog>
    </div>
  );
}
