import axios from '@/lib/axios';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Shield } from 'lucide-react';
import { toast } from 'sonner';
import { useCallback, useEffect, useState } from 'react';

// Import the LeagueMember type from models.d.ts
import { type LeagueMember } from '@/types/models';

interface LeagueMemberManagerProps {
  members: LeagueMember[];
  maxTeams: number;
  onMembersChange: (members: LeagueMember[]) => void;
}

export default function LeagueMemberManager({ members, maxTeams, onMembersChange }: LeagueMemberManagerProps) {
  // Only keeping the state variables needed for the visible UI
  const [draftPositionDialogOpen, setDraftPositionDialogOpen] = useState(false);
  const [selectedMember, setSelectedMember] = useState<LeagueMember | null>(null);
  const [draftPosition, setDraftPosition] = useState<number | null>(null);
  const [draftPositionError, setDraftPositionError] = useState<string>('');
  const [updatingDraftPosition, setUpdatingDraftPosition] = useState(false);

  // const teamColumns = (members?.length % 5 === 0) ? 5 : 4;
  // const teamColumns = 3;

  const teamCount = members?.length || 0;
  const teamColumns = teamCount % 5 === 0 ? '5' : teamCount % 4 === 0 ? '4' : '3';
  const teamGridCols = `grid gap-4 grid-cols-${teamColumns}`;

  // Validate draft position
  const validateDraftPosition = useCallback(() => {
    if (draftPosition !== null && draftPosition !== 0) {
      if (draftPosition < 1 || draftPosition > maxTeams) {
        setDraftPositionError(`Draft position must be between 1 and ${maxTeams}`);
        return false;
      }

      // Check if this draft position is already taken by another member
      const existingMember = members.find((m) => m.id !== selectedMember?.id && m.draft_position === draftPosition);

      if (existingMember) {
        setDraftPositionError(`Draft position ${draftPosition} is already assigned to ${existingMember.team_name}`);
        return false;
      }
    }

    setDraftPositionError('');
    return true;
  }, [draftPosition, maxTeams, members, selectedMember]);

  // Validate draft position when it changes
  useEffect(() => {
    if (draftPositionDialogOpen) {
      validateDraftPosition();
    }
  }, [draftPositionDialogOpen, validateDraftPosition]);

  const handleUpdateDraftPosition = async () => {
    if (!selectedMember) return;

    if (!validateDraftPosition()) {
      toast.error(draftPositionError);
      return;
    }

    try {
      setUpdatingDraftPosition(true);
      const response = await axios.patch(`/api/league-members/${selectedMember.id}/draft-position`, {
        draft_position: draftPosition,
      });

      toast('Draft position updated successfully');

      // Update the member in the list
      onMembersChange(members.map((m) => (m.id === selectedMember.id ? response.data as LeagueMember : m)));

      setDraftPositionDialogOpen(false);
    } catch (err: unknown) {
      const errorMsg = err instanceof Error ? err.message : 'Failed to update draft position';
      toast.error(errorMsg);
    } finally {
      setUpdatingDraftPosition(false);
    }
  };

  // Function to open the draft position dialog
  // const openDraftPositionDialog = (member: LeagueMember) => {
  //   setSelectedMember(member);
  //   setDraftPosition(member.draft_position);
  //   setDraftPositionError('');
  //   setDraftPositionDialogOpen(true);
  // };

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h3 className="text-lg font-medium">
          League Members ( {members.length} / {maxTeams} )
        </h3>
      </div>

      {/* IDK why I had to do it this way, but it wasn't working otherwise */}
      <div className={teamGridCols ? `grid gap-4 grid-cols-${teamColumns}` : 'grid gap-4 grid-cols-3'}>
        {members.map((member) => (
          <div key={member.id} className="col-span-1 flex items-center justify-between rounded-md border p-3">
            <div className="flex items-center justify-between w-full">
              <div className="grow-0">
                <Avatar>
                  {member.team_logo ? <AvatarImage src={member.team_logo} alt={member.team_name} /> : null}
                  <AvatarFallback>{member.team_name.substring(0, 2).toUpperCase()}</AvatarFallback>
                </Avatar>
              </div>
              <div className="grow-1 grid grid-cols-4 pl-2">
                <div className="col-span-3">
                  <p className="font-medium">{member.team_name}</p>
                  <p className="text-sm text-gray-500 dark:text-gray-400">
                    {member.user?.name}
                  </p>
                </div>
                <div className="col-span-1 flex items-start justify-end">
                    {member.is_admin && (
                      <Badge variant="outline" className="ml-2">
                        <Shield size={12} className="mr-1" />
                        Admin
                      </Badge>
                    )}
                </div>
              </div>
            </div>
          </div>
        ))}

        {members.length === 0 && (
          <div className="rounded-md border py-8 text-center">
            <p className="text-gray-500 dark:text-gray-400">No members yet</p>
          </div>
        )}
      </div>

      <Dialog open={draftPositionDialogOpen} onOpenChange={setDraftPositionDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Set Draft Position</DialogTitle>
            <DialogDescription>{selectedMember?.team_name}</DialogDescription>
          </DialogHeader>
          <div className="py-4">
            <div className="flex items-center gap-4">
              <Label htmlFor="draftPosition">Draft Position</Label>
              <Input
                id="draftPosition"
                type="number"
                min={1}
                max={maxTeams}
                value={draftPosition || ''}
                onChange={(e) => setDraftPosition(e.target.value ? parseInt(e.target.value) : null)}
                className={draftPositionError ? 'border-red-500' : ''}
              />
              {draftPositionError && <p className="mt-1 text-sm text-red-500">{draftPositionError}</p>}
            </div>
            <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">Set to 0 or leave empty to clear the draft position.</p>
          </div>
          <DialogFooter>
            <Button onClick={handleUpdateDraftPosition} disabled={updatingDraftPosition}>
              {updatingDraftPosition ? 'Saving...' : 'Save'}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
