import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { X, ChevronUp, ChevronDown } from 'lucide-react';

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

export default function RosterPositionsEditor({ positions, onChange }: RosterPositionsEditorProps) {
  const [selectedPosition, setSelectedPosition] = useState<string>('');

  const addPosition = () => {
    if (selectedPosition && !positions.includes(selectedPosition)) {
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
    if (
      (direction === 'up' && index === 0) ||
      (direction === 'down' && index === positions.length - 1)
    ) {
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
    <div className="space-y-4">
      <div>
        <Label htmlFor="roster-positions">Starting Lineup Positions</Label>
        <div className="flex flex-wrap gap-2 p-2 border rounded-md min-h-[100px] mt-2">
          {positions.length === 0 ? (
            <p className="text-sm text-gray-500 dark:text-gray-400 w-full text-center py-4">
              No positions added yet. Add positions below.
            </p>
          ) : (
            positions.map((position, index) => (
              <Badge key={`${position}-${index}`} variant="secondary" className="flex items-center gap-1 py-1 px-2">
                <span>{position}</span>
                <div className="flex items-center ml-1">
                  <button
                    type="button"
                    onClick={() => movePosition(index, 'up')}
                    disabled={index === 0}
                    className="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 disabled:opacity-30"
                  >
                    <ChevronUp size={14} />
                  </button>
                  <button
                    type="button"
                    onClick={() => movePosition(index, 'down')}
                    disabled={index === positions.length - 1}
                    className="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 disabled:opacity-30"
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
            ))
          )}
        </div>
      </div>

      <div className="flex items-end gap-2">
        <div className="flex-1">
          <Label htmlFor="add-position">Add Position</Label>
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
        <Button type="button" onClick={addPosition} disabled={!selectedPosition}>
          Add
        </Button>
      </div>

      <div className="text-sm text-gray-500 dark:text-gray-400">
        <p>Order matters! The positions will be displayed in the order they appear above.</p>
      </div>
    </div>
  );
}
