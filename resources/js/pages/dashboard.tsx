import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
  } from "@/components/ui/table"
import { useEffect, useState } from 'react';

// No breadcrumbs needed for React Router implementation

export default function Dashboard() {
    const [teams, setTeams] = useState<Team[]>([]);
    const [loading, setLoading] = useState<boolean>(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const fetchTeams = async () => {
            console.log('fetching teams')
            try {
                setLoading(true);
                const response = await axios.get<Team[]>('/api/teams');
                setTeams(response.data);
                setError(null);
            } catch (err) {
                console.error('Error fetching teams:', err);
                setError('Failed to load teams. Please try again later.');
            } finally {
                setLoading(false);
            }
        };

        fetchTeams();
    }, []);

    return (
        <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                </div>
                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    {loading ? (
                        <div className="flex items-center justify-center h-64">
                            <p className="text-lg">Loading teams...</p>
                        </div>
                    ) : error ? (
                        <div className="flex items-center justify-center h-64">
                            <p className="text-lg text-red-500">{error}</p>
                        </div>
                    ) : (
                        <Table>
                            <TableCaption>NFL Teams</TableCaption>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-[80px]">ID</TableHead>
                                    <TableHead>Abbreviation</TableHead>
                                    <TableHead>Team</TableHead>
                                    <TableHead>Conference</TableHead>
                                    <TableHead>Division</TableHead>
                                    <TableHead className="text-right">ESPN ID</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {teams.map((team) => (
                                    <TableRow key={team.id}>
                                        <TableCell className="font-medium">{team.id}</TableCell>
                                        <TableCell>{team.abbreviation}</TableCell>
                                        <TableCell>{team.location} {team.name}</TableCell>
                                        <TableCell>{team.conference}</TableCell>
                                        <TableCell>{team.division}</TableCell>
                                        <TableCell className="text-right">{team.espn_id}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                </div>
            </div>
    );
}
