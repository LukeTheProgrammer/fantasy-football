import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ChevronDown, ChevronUp, X } from 'lucide-react';
import { useState } from 'react';

interface RosterPositionsEditorProps {
  positions: string[];
  onChange: (positions: string[]) => void;
}

const AVAILABLE_POSITIONS = [
  { value: 'QB', label: 'Quarterback (QB)' },
  { value: 'RB', label: 'Running Back (RB)' },
  { value: 'WR', label: 'Wide Receiver (WR)' },
  { value: 'TE', label: 'Tight End (TE)' },
  { value: 'K', label: 'Kicker (K)' },
  { value: 'DEF', label: 'Defense (DEF)' },
  { value: 'FLEX', label: 'Flex (RB/WR/TE)' },
  { value: 'SUPERFLEX', label: 'Super Flex (QB/RB/WR/TE)' },
  { value: 'IDP', label: 'Individual Defensive Player (IDP)' },
  { value: 'DL', label: 'Defensive Line (DL)' },
  { value: 'LB', label: 'Linebacker (LB)' },
  { value: 'DB', label: 'Defensive Back (DB)' },
];

export function RosterPositionsEditor({ positions, onChange }: RosterPositionsEditorProps) {
  const [selectedPosition, setSelectedPosition] = useState<string>('');

  const addPosition = () => {
    if (selectedPosition) {
      const newPositions = [...positions, selectedPosition];
      onChange(newPositions);
      setSelectedPosition('');
    }
  };

  const removePosition = (index: number) => {
    const newPositions = [...positions];
    newPositions.splice(index, 1);
    onChange(newPositions);
  };

  const movePosition = (index: number, direction: 'up' | 'down') => {
    if ((direction === 'up' && index === 0) || (direction === 'down' && index === positions.length - 1)) {
      return;
    }

    const newPositions = [...positions];
    const newIndex = direction === 'up' ? index - 1 : index + 1;
    const temp = newPositions[index];
    newPositions[index] = newPositions[newIndex];
    newPositions[newIndex] = temp;
    onChange(newPositions);
  };

  return (
    <div className="mt-4 space-y-4">
      <div>
        <Label htmlFor="add-position">Add Position</Label>
        <div className="mt-4 flex items-center justify-between gap-2">
          <div className="w-full grow-1 px-0">
            <Select value={selectedPosition} onValueChange={setSelectedPosition}>
              <SelectTrigger id="add-position">
                <SelectValue placeholder="Select position" />
              </SelectTrigger>
              <SelectContent>
                {AVAILABLE_POSITIONS.map((position) => (
                  <SelectItem key={position.value} value={position.value}>
                    {position.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          <div className="grow-0 px-0">
            <Button type="button" className="w-full" onClick={addPosition} disabled={!selectedPosition}>
              Add
            </Button>
          </div>
        </div>
      </div>
      <div>
        <div className="mt-1 flex items-start justify-between gap-2">
          <Label htmlFor="roster-positions">Starting Lineup</Label>
          <div className="text-sm text-gray-500 dark:text-gray-400">
            <p className="text-xs">Order matters!</p>
          </div>
        </div>
        <div className="mt-2 flex min-h-[100px] flex-wrap gap-2 rounded-md border p-2">
          {positions.length === 0 ? (
            <p className="w-full py-4 text-center text-sm text-gray-500 dark:text-gray-400">No positions added yet. Add positions below.</p>
          ) : (
            <div className="flex w-full flex-col items-center justify-start">
              {positions.map((position, index) => (
                <div key={`${position}-${index}`} className="flex w-full items-center justify-center gap-2">
                  <Badge variant="secondary" className="mb-2 flex items-center gap-1 px-2 py-1">
                    <span style={{ minWidth: '5em' }}>{position}</span>
                    <div className="ml-1 flex items-center">
                      <button
                        type="button"
                        onClick={() => movePosition(index, 'up')}
                        disabled={index === 0}
                        className="mr-3 text-gray-500 hover:text-gray-700 disabled:opacity-30 dark:text-gray-400 dark:hover:text-gray-200"
                      >
                        <ChevronUp size={14} />
                      </button>
                      <button
                        type="button"
                        onClick={() => movePosition(index, 'down')}
                        disabled={index === positions.length - 1}
                        className="mr-3 text-gray-500 hover:text-gray-700 disabled:opacity-30 dark:text-gray-400 dark:hover:text-gray-200"
                      >
                        <ChevronDown size={14} />
                      </button>
                      <button
                        type="button"
                        onClick={() => removePosition(index)}
                        className="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                      >
                        <X size={14} />
                      </button>
                    </div>
                  </Badge>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
