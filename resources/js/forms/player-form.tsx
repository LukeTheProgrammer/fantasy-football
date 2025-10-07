import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { X, Plus } from 'lucide-react';

interface PlayerAlias {
  name: string;
}

interface PlayerFormData {
  first_name: string;
  last_name: string;
  full_name: string;
  height: string;
  weight: string;
  college: string;
  draft_year: string;
  jersey_number: string;
  aliases: PlayerAlias[];
}

interface PlayerFormProps {
  formData: PlayerFormData;
  onChange: (data: PlayerFormData) => void;
}

export default function PlayerForm({ formData, onChange }: PlayerFormProps) {
  const handleInputChange = (field: keyof PlayerFormData, value: string) => {
    const updatedData = {
      ...formData,
      [field]: value
    };

    // Automatically calculate full_name when first_name or last_name changes
    if (field === 'first_name' || field === 'last_name') {
      const firstName = field === 'first_name' ? value : formData.first_name;
      const lastName = field === 'last_name' ? value : formData.last_name;
      updatedData.full_name = `${firstName} ${lastName}`.trim();
    }

    onChange(updatedData);
  };

  const handleAddAlias = () => {
    const updatedData = {
      ...formData,
      aliases: [...formData.aliases, { name: '' }]
    };
    onChange(updatedData);
  };

  const handleUpdateAlias = (index: number, name: string) => {
    const updatedAliases = [...formData.aliases];
    updatedAliases[index] = { name };
    const updatedData = {
      ...formData,
      aliases: updatedAliases
    };
    onChange(updatedData);
  };

  const handleDeleteAlias = (index: number) => {
    const updatedAliases = formData.aliases.filter((_, i) => i !== index);
    const updatedData = {
      ...formData,
      aliases: updatedAliases
    };
    onChange(updatedData);
  };

  return (
    <div className="grid gap-4 py-4">
      <div className="grid grid-cols-2 gap-4">
        <div className="grid gap-2">
          <Label htmlFor="first_name">First Name</Label>
          <Input
            id="first_name"
            value={formData.first_name}
            onChange={(e) => handleInputChange('first_name', e.target.value)}
          />
        </div>
        <div className="grid gap-2">
          <Label htmlFor="last_name">Last Name</Label>
          <Input
            id="last_name"
            value={formData.last_name}
            onChange={(e) => handleInputChange('last_name', e.target.value)}
          />
        </div>
      </div>

      <div className="grid gap-2">
        <Label htmlFor="jersey_number">Jersey Number</Label>
        <Input
          id="jersey_number"
          value={formData.jersey_number}
          onChange={(e) => handleInputChange('jersey_number', e.target.value)}
          placeholder="e.g., 12"
        />
      </div>

      <div className="grid grid-cols-2 gap-4">
        <div className="grid gap-2">
          <Label htmlFor="height">Height</Label>
          <Input
            id="height"
            value={formData.height}
            onChange={(e) => handleInputChange('height', e.target.value)}
            placeholder="e.g., 6'2&quot;"
          />
        </div>
        <div className="grid gap-2">
          <Label htmlFor="weight">Weight</Label>
          <Input
            id="weight"
            value={formData.weight}
            onChange={(e) => handleInputChange('weight', e.target.value)}
            placeholder="e.g., 215"
          />
        </div>
      </div>

      <div className="grid gap-2">
        <Label htmlFor="college">College</Label>
        <Input
          id="college"
          value={formData.college}
          onChange={(e) => handleInputChange('college', e.target.value)}
        />
      </div>

      <div className="grid gap-2">
        <Label htmlFor="draft_year">Draft Year</Label>
        <Input
          id="draft_year"
          value={formData.draft_year}
          onChange={(e) => handleInputChange('draft_year', e.target.value)}
          placeholder="e.g., 2020"
        />
      </div>

      <div className="grid gap-2">
        <div className="flex items-center justify-between">
          <Label>Aliases</Label>
          <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={handleAddAlias}
            className="flex items-center gap-1"
          >
            <Plus className="h-4 w-4" />
            Add Alias
          </Button>
        </div>
        
        {formData.aliases.length === 0 ? (
          <p className="text-sm text-gray-500 dark:text-gray-400">
            No aliases added yet. Click "Add Alias" to add alternative names for this player.
          </p>
        ) : (
          <div className="space-y-2">
            {formData.aliases.map((alias, index) => (
              <div key={index} className="flex items-center gap-2">
                <Input
                  value={alias.name}
                  onChange={(e) => handleUpdateAlias(index, e.target.value)}
                  placeholder="Enter alias name..."
                  className="flex-1"
                />
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => handleDeleteAlias(index)}
                  className="flex items-center gap-1 text-red-600 hover:text-red-700"
                >
                  <X className="h-4 w-4" />
                </Button>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}

export type { PlayerFormData };
