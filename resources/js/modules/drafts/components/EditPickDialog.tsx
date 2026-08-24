import { FormDialog } from '@/common/form-dialog/FormDialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { type AuctionPlayer, type AuctionTeam } from '@/types/models';
import { useForm } from '@inertiajs/react';

interface EditPickDialogProps {
  /** The picked player being corrected, or null when the dialog is closed. */
  player: AuctionPlayer | null;
  teams: AuctionTeam[];
  draftId: number;
  onClose: () => void;
}

/**
 * Corrects a pick already recorded: who took him, and for how much.
 *
 * The player himself is not editable. A pick against the wrong player is undone
 * from the list instead, since the board already counts him as gone.
 */
export function EditPickDialog({ player, teams, draftId, onClose }: EditPickDialogProps) {
  const { data, setData, patch, processing, errors, clearErrors } = useForm({
    league_member_id: String(player?.drafted_by ?? ''),
    amount: String(player?.drafted_for ?? ''),
  });

  if (!player || !player.pick_id) {
    return null;
  }

  const handleSave = () => {
    patch(route('drafts.picks.update', [draftId, player.pick_id]), {
      preserveScroll: true,
      onSuccess: () => {
        clearErrors();
        onClose();
      },
    });
  };

  const handleOpenChange = (open: boolean) => {
    if (!open) {
      onClose();
    }
  };

  return (
    <FormDialog
      open={true}
      onOpenChange={handleOpenChange}
      title={`${player.full_name}`}
      saveLabel="Update"
      onSave={handleSave}
      onCancel={onClose}
      isLoading={processing}
      saveDisabled={!data.league_member_id || !data.amount}
      maxWidth="sm:max-w-[420px]"
    >
      <div className="space-y-4">
        <div className="space-y-2">
          <Label htmlFor="edit-pick-team">Picked by</Label>
          <Select value={data.league_member_id} onValueChange={(value) => setData('league_member_id', value)}>
            <SelectTrigger id="edit-pick-team" className="w-full">
              <SelectValue placeholder="Select team" />
            </SelectTrigger>
            <SelectContent>
              {teams.map((team) => (
                <SelectItem key={team.id} value={String(team.id)}>
                  {team.team_name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>

        <div className="space-y-2">
          <Label htmlFor="edit-pick-amount">Amount</Label>
          <Input
            id="edit-pick-amount"
            type="number"
            min={1}
            inputMode="numeric"
            placeholder="$"
            value={data.amount}
            onChange={(event) => setData('amount', event.target.value)}
          />
        </div>

        {Object.values(errors).length > 0 && <p className="text-sm text-destructive">{Object.values(errors)[0]}</p>}
      </div>
    </FormDialog>
  );
}
