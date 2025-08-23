import { useState, useEffect, useCallback } from 'react';
import axios from 'axios';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useToast } from '@/components/ui/use-toast';
import { MoreHorizontal, UserPlus, Shield } from 'lucide-react';

interface User {
  id: number;
  name: string;
  email: string;
}

interface LeagueMember {
  id: number;
  league_id: number;
  user_id: number;
  team_name: string;
  team_logo: string | null;
  draft_position: number | null;
  is_admin: boolean;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  user: User;
}

interface LeagueMemberManagerProps {
  leagueId: number;
  members: LeagueMember[];
  maxTeams: number;
  userIsAdmin: boolean;
  currentUserId: number;
  onMembersChange: (members: LeagueMember[]) => void;
}

export default function LeagueMemberManager({
  leagueId,
  members,
  maxTeams,
  userIsAdmin,
  currentUserId,
  onMembersChange
}: LeagueMemberManagerProps) {
  const { toast } = useToast();
  const [inviteDialogOpen, setInviteDialogOpen] = useState(false);
  const [inviteEmail, setInviteEmail] = useState('');
  const [inviteTeamName, setInviteTeamName] = useState('');
  const [inviteErrors, setInviteErrors] = useState<Record<string, string>>({});
  const [formSubmitted, setFormSubmitted] = useState(false);
  const [inviting, setInviting] = useState(false);
  const [draftPositionDialogOpen, setDraftPositionDialogOpen] = useState(false);
  const [selectedMember, setSelectedMember] = useState<LeagueMember | null>(null);
  const [draftPosition, setDraftPosition] = useState<number | null>(null);
  const [draftPositionError, setDraftPositionError] = useState<string>('');
  const [updatingDraftPosition, setUpdatingDraftPosition] = useState(false);

  // Validate invite form
  const validateInviteForm = useCallback(() => {
    const errors: Record<string, string> = {};
    
    if (!inviteEmail.trim()) {
      errors.email = 'Email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(inviteEmail)) {
      errors.email = 'Please enter a valid email address';
    }
    
    if (!inviteTeamName.trim()) {
      errors.teamName = 'Team name is required';
    } else if (inviteTeamName.length < 3) {
      errors.teamName = 'Team name must be at least 3 characters';
    } else if (inviteTeamName.length > 30) {
      errors.teamName = 'Team name must be less than 30 characters';
    }
    
    setInviteErrors(errors);
    return Object.keys(errors).length === 0;
  }, [inviteEmail, inviteTeamName]);
  
  // Update validation when form data changes
  useEffect(() => {
    if (formSubmitted) {
      validateInviteForm();
    }
  }, [formSubmitted, validateInviteForm]);

  const handleInviteMember = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormSubmitted(true);
    
    if (!validateInviteForm()) {
      toast({
        title: "Validation Error",
        description: "Please fix the errors in the form",
        variant: "destructive"
      });
      return;
    }
    
    try {
      setInviting(true);
      const response = await axios.post('/api/league-members', {
        league_id: leagueId,
        email: inviteEmail,
        team_name: inviteTeamName
      });
      
      toast({
        title: "Success",
        description: "Member invited successfully"
      });
      
      // Add the new member to the list
      onMembersChange([...members, response.data]);
      
      // Reset form
      setInviteEmail('');
      setInviteTeamName('');
      setInviteErrors({});
      setFormSubmitted(false);
      setInviteDialogOpen(false);
    } catch (err: unknown) {
      const errorMsg = err instanceof Error ? err.message : "Failed to invite member";
      toast({
        title: "Error",
        description: errorMsg,
        variant: "destructive"
      });
    } finally {
      setInviting(false);
    }
  };

  const handleRemoveMember = async (memberId: number) => {
    if (!confirm('Are you sure you want to remove this member?')) {
      return;
    }
    
    try {
      await axios.delete(`/api/league-members/${memberId}`);
      
      toast({
        title: "Success",
        description: "Member removed successfully"
      });
      
      // Remove the member from the list
      onMembersChange(members.filter(member => member.id !== memberId));
    } catch (err: unknown) {
      const errorMsg = err instanceof Error ? err.message : "Failed to remove member";
      toast({
        title: "Error",
        description: errorMsg,
        variant: "destructive"
      });
    }
  };

  const handleToggleAdmin = async (member: LeagueMember) => {
    try {
      const response = await axios.put(`/api/league-members/${member.id}`, {
        ...member,
        is_admin: !member.is_admin
      });
      
      toast({
        title: "Success",
        description: `Admin status ${member.is_admin ? 'removed' : 'granted'}`
      });
      
      // Update the member in the list
      onMembersChange(members.map(m => m.id === member.id ? response.data : m));
    } catch (err: unknown) {
      const errorMsg = err instanceof Error ? err.message : "Failed to update admin status";
      toast({
        title: "Error",
        description: errorMsg,
        variant: "destructive"
      });
    }
  };

  const openDraftPositionDialog = (member: LeagueMember) => {
    setSelectedMember(member);
    setDraftPosition(member.draft_position);
    setDraftPositionError('');
    setDraftPositionDialogOpen(true);
  };

  const validateDraftPosition = useCallback(() => {
    if (draftPosition !== null && draftPosition !== 0) {
      if (draftPosition < 1 || draftPosition > maxTeams) {
        setDraftPositionError(`Draft position must be between 1 and ${maxTeams}`);
        return false;
      }
      
      // Check if this draft position is already taken by another member
      const existingMember = members.find(m => 
        m.id !== selectedMember?.id && 
        m.draft_position === draftPosition
      );
      
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
      toast({
        title: "Validation Error",
        description: draftPositionError,
        variant: "destructive"
      });
      return;
    }
    
    try {
      setUpdatingDraftPosition(true);
      const response = await axios.patch(`/api/league-members/${selectedMember.id}/draft-position`, {
        draft_position: draftPosition
      });
      
      toast({
        title: "Success",
        description: "Draft position updated successfully"
      });
      
      // Update the member in the list
      onMembersChange(members.map(m => m.id === selectedMember.id ? response.data : m));
      
      setDraftPositionDialogOpen(false);
    } catch (err: unknown) {
      const errorMsg = err instanceof Error ? err.message : "Failed to update draft position";
      toast({
        title: "Error",
        description: errorMsg,
        variant: "destructive"
      });
    } finally {
      setUpdatingDraftPosition(false);
    }
  };

  const isLastAdmin = (member: LeagueMember) => {
    return member.is_admin && members.filter(m => m.is_admin).length === 1;
  };

  return (
    <div className="space-y-4">
      <div className="flex justify-between items-center">
        <h3 className="text-lg font-medium">League Members ({members.length}/{maxTeams})</h3>
        {userIsAdmin && members.length < maxTeams && (
          <Dialog open={inviteDialogOpen} onOpenChange={setInviteDialogOpen}>
            <DialogTrigger asChild>
              <Button size="sm" className="flex items-center gap-1">
                <UserPlus size={16} />
                <span>Invite Member</span>
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Invite New Member</DialogTitle>
                <DialogDescription>
                  Add a new member to your fantasy league.
                </DialogDescription>
              </DialogHeader>
              <form onSubmit={handleInviteMember}>
                <div className="grid gap-4 py-4">
                  <div className="grid grid-cols-4 items-center gap-4">
                    <Label htmlFor="email" className="text-right">
                      Email
                    </Label>
                    <Input
                      id="email"
                      type="email"
                      value={inviteEmail}
                      onChange={(e) => setInviteEmail(e.target.value)}
                      className={`col-span-3 ${inviteErrors.email ? 'border-red-500' : ''}`}
                      placeholder="member@example.com"
                    />
                    {inviteErrors.email && (
                      <p className="text-sm text-red-500 col-span-3 col-start-2">{inviteErrors.email}</p>
                    )}
                  </div>
                  <div className="grid grid-cols-4 items-center gap-4">
                    <Label htmlFor="teamName" className="text-right">
                      Team Name
                    </Label>
                    <Input
                      id="teamName"
                      value={inviteTeamName}
                      onChange={(e) => setInviteTeamName(e.target.value)}
                      className={`col-span-3 ${inviteErrors.teamName ? 'border-red-500' : ''}`}
                      placeholder="Team Name"
                    />
                    {inviteErrors.teamName && (
                      <p className="text-sm text-red-500 col-span-3 col-start-2">{inviteErrors.teamName}</p>
                    )}
                  </div>
                </div>
                <DialogFooter>
                  <Button type="submit" disabled={inviting}>
                    {inviting ? 'Inviting...' : 'Invite Member'}
                  </Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>
        )}
      </div>

      <div className="space-y-4">
        {members.map(member => (
          <div key={member.id} className="flex items-center justify-between p-3 border rounded-md">
            <div className="flex items-center space-x-4">
              <Avatar>
                {member.team_logo ? (
                  <AvatarImage src={member.team_logo} alt={member.team_name} />
                ) : null}
                <AvatarFallback>
                  {member.team_name.substring(0, 2).toUpperCase()}
                </AvatarFallback>
              </Avatar>
              <div>
                <p className="font-medium">{member.team_name}</p>
                <p className="text-sm text-gray-500 dark:text-gray-400">
                  {member.user.name}
                  {member.is_admin && (
                    <Badge variant="outline" className="ml-2">
                      <Shield size={12} className="mr-1" />
                      Admin
                    </Badge>
                  )}
                  {member.user_id === currentUserId && (
                    <Badge variant="secondary" className="ml-2">You</Badge>
                  )}
                </p>
              </div>
            </div>
            
            <div className="flex items-center gap-2">
              <div className="text-sm">
                {member.draft_position !== null ? (
                  <Badge variant="secondary">Pick #{member.draft_position}</Badge>
                ) : (
                  <span className="text-gray-500 dark:text-gray-400">No draft position</span>
                )}
              </div>
              
              {userIsAdmin && (
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="sm">
                      <MoreHorizontal size={16} />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuItem onClick={() => openDraftPositionDialog(member)}>
                      Set Draft Position
                    </DropdownMenuItem>
                    
                    {member.user_id !== currentUserId && (
                      <DropdownMenuItem
                        onClick={() => handleToggleAdmin(member)}
                        disabled={isLastAdmin(member)}
                      >
                        {member.is_admin ? 'Remove Admin' : 'Make Admin'}
                      </DropdownMenuItem>
                    )}
                    
                    {(userIsAdmin || member.user_id === currentUserId) && (
                      <DropdownMenuItem 
                        onClick={() => handleRemoveMember(member.id)}
                        disabled={isLastAdmin(member)}
                        className="text-red-600 focus:text-red-600"
                      >
                        Remove Member
                      </DropdownMenuItem>
                    )}
                  </DropdownMenuContent>
                </DropdownMenu>
              )}
            </div>
          </div>
        ))}
        
        {members.length === 0 && (
          <div className="text-center py-8 border rounded-md">
            <p className="text-gray-500 dark:text-gray-400">No members yet</p>
          </div>
        )}
      </div>

      <Dialog open={draftPositionDialogOpen} onOpenChange={setDraftPositionDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Set Draft Position</DialogTitle>
            <DialogDescription>
              {selectedMember?.team_name}
            </DialogDescription>
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
              {draftPositionError && (
                <p className="text-sm text-red-500 mt-1">{draftPositionError}</p>
              )}
            </div>
            <p className="text-sm text-gray-500 dark:text-gray-400 mt-2">
              Set to 0 or leave empty to clear the draft position.
            </p>
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
