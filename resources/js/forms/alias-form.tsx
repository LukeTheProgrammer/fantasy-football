import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { useState } from 'react';
import axios from 'axios';

interface AliasFormData {
  name: string;
  player_id: string;
}

interface Player {
  id: number;
  full_name: string;
  position_id: string;
  team_id: string | null;
}

interface AliasFormProps {
  formData: AliasFormData;
  onChange: (data: AliasFormData) => void;
  playerInfo?: {
    full_name: string;
    position: string;
    team: string | null;
  };
}

export default function AliasForm({ formData, onChange, playerInfo }: AliasFormProps) {
  const [searchQuery, setSearchQuery] = useState('');
  const [searchResults, setSearchResults] = useState<Player[]>([]);
  const [isSearching, setIsSearching] = useState(false);
  const [showResults, setShowResults] = useState(false);
  const [selectedPlayer, setSelectedPlayer] = useState<Player | null>(null);

  const handleInputChange = (field: keyof AliasFormData, value: string) => {
    const updatedData = {
      ...formData,
      [field]: value
    };

    console.log(updatedData);

    onChange(updatedData);
  };

  const handleSearch = async () => {
    if (!searchQuery.trim()) return;

    setIsSearching(true);
    try {
      const response = await axios.post('/api/players/search', {
        search: searchQuery
      });
      setSearchResults(response.data);
      setShowResults(true);
    } catch (error) {
      console.error('Error searching players:', error);
      setSearchResults([]);
    } finally {
      setIsSearching(false);
    }
  };

  const handleSelectPlayer = (player: Player) => {
    const updatedData = {
      ...formData,
      player_id: player.id.toString()
    };
    onChange(updatedData);
    setSelectedPlayer(player);
    setShowResults(false);
    setSearchQuery('');
    setSearchResults([]);
  };

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      handleSearch();
    }
  };

  return (
    <div className="grid gap-4 py-4">
      <div className="grid gap-2">
        <Label htmlFor="alias-name">Alias Name</Label>
        <Input
          id="alias-name"
          value={formData.name}
          onChange={(e) => handleInputChange('name', e.target.value)}
          placeholder="Enter alias name..."
        />
      </div>

      {playerInfo && (
        <div className="grid gap-2">
          <Label>Current Player</Label>
          <div className="text-sm text-gray-600 dark:text-gray-400 p-3 bg-gray-50 dark:bg-gray-800 rounded-md">
            {playerInfo.full_name} {playerInfo.position} {playerInfo.team}
          </div>
        </div>
      )}

      <div className="grid gap-2">
        <Label>Change Player</Label>
        <div className="flex gap-2">
          <Input
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            onKeyPress={handleKeyPress}
            placeholder="Search for a player..."
            className="flex-1"
          />
          <Button
            type="button"
            onClick={handleSearch}
            disabled={isSearching || !searchQuery.trim()}
            variant="outline"
          >
            {isSearching ? 'Searching...' : 'Search'}
          </Button>
        </div>

        {showResults && (
          <div className="mt-2 border rounded-md max-h-48 overflow-y-auto">
            {searchResults.length === 0 ? (
              <div className="p-3 text-sm text-gray-500 dark:text-gray-400">
                No players found for "{searchQuery}"
              </div>
            ) : (
              <div className="divide-y">
                {searchResults.map((player) => (
                  <button
                    key={player.id}
                    type="button"
                    onClick={() => handleSelectPlayer(player)}
                    className="w-full p-3 text-left hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                  >
                    <div className="font-medium">{player.full_name}</div>
                    <div className="text-sm text-gray-500 dark:text-gray-400">
                      {player.position_id} - {player.team_id || 'FA'}
                    </div>
                  </button>
                ))}
              </div>
            )}
          </div>
        )}
      </div>

      {selectedPlayer && (
        <div className="grid gap-2">
          <Label>New Player</Label>
          <div className="text-sm text-green-700 dark:text-green-400 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
            <div className="flex items-center gap-2">
              <svg className="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
              </svg>
              <span className="font-medium">Selected:</span>
              {selectedPlayer.full_name} ({selectedPlayer.position_id}) - {selectedPlayer.team_id || 'FA'}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export type { AliasFormData };
