import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Link } from '@inertiajs/react';
import {
    Trophy,
    Play,
    Star,
    Search,
    ChevronRight,
    Users,
    Building,
    Plus
} from 'lucide-react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex-1 p-8">
                <div className="mb-8">
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h1 className="mb-2 text-3xl">My Fantasy Leagues</h1>
                            <p className="">Manage and track your fantasy football leagues</p>
                        </div>
                        <Link href="/leagues/create">
                            <Button size="lg" variant="secondary" className="cursor-pointer">
                                <span className="flex justify-between">
                                    <Plus className="h-5 w-5 pt-1" strokeWidth={4} />
                                    <span className="pl-1">Create New League</span>
                                </span>
                            </Button>
                        </Link>
                    </div>
                </div>

                <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div className="rounded-lg border p-6 bg-card">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm">Total Leagues</p>
                                <p className="text-2xl">4</p>
                            </div>
                            <div className="rounded-lg p-3">
                                <Trophy className="h-5 w-5 " />
                            </div>
                        </div>
                    </div>
                    <div className="rounded-lg border p-6 bg-card">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm">Active Drafts</p>
                                <p className="text-2xl">2</p>
                            </div>
                            <div className="rounded-lg p-3">
                                <Play className="h-5 w-5 " />
                            </div>
                        </div>
                    </div>
                    <div className="rounded-lg border p-6 bg-card">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm">Wins This Season</p>
                                <p className="text-2xl">12</p>
                            </div>
                            <div className="rounded-lg p-3">
                                <Star className="h-5 w-5 " />
                            </div>
                        </div>
                    </div>
                </div>

                <div className="rounded-lg border bg-card">
                    <div className="border-b p-6">
                        <div className="flex items-center justify-between">
                            <h2 className="text-xl">Your Leagues</h2>
                            <div className="flex items-center space-x-4">
                                <div className="relative">
                                    <Input
                                        type="text"
                                        placeholder="Search leagues..."
                                        className="rounded-full border py-2 pr-4 pl-10 text-sm focus:ring-2"
                                    />
                                    <Search className="absolute top-1/2 left-3 -translate-y-1/2 transform h-4 w-4" />
                                </div>
                                <Select>
                                    <SelectTrigger className="w-[180px]">
                                        <SelectValue placeholder="Filter Leagues" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Leagues</SelectItem>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="completed">Completed</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </div>

                    <div className="divide-y">
                        <div className="p-6">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center space-x-4">
                                    <div className="flex h-12 w-12 items-center justify-center rounded-lg">
                                        <Trophy className="h-5 w-5 " />
                                    </div>
                                    <div>
                                        <h3 className="">Championship League 2025</h3>
                                        <p className="text-sm">12 teams • PPR • Snake Draft</p>
                                        <p className="text-sm">Draft: March 15, 2025 at 8:00 PM</p>
                                    </div>
                                </div>
                                <div className="flex items-center space-x-4">
                                    <span className="rounded-full px-3 py-1 text-sm">Active</span>
                                    <button className="">
                                        <ChevronRight className="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div className="p-6">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center space-x-4">
                                    <div className="flex h-12 w-12 items-center justify-center rounded-lg">
                                        <Users className="h-5 w-5 " />
                                    </div>
                                    <div>
                                        <h3 className="">Friends & Family League</h3>
                                        <p className="text-sm">10 teams • Standard • Auction Draft</p>
                                        <p className="text-sm">Draft: March 20, 2025 at 7:00 PM</p>
                                    </div>
                                </div>
                                <div className="flex items-center space-x-4">
                                    <span className="rounded-full px-3 py-1 text-sm">Drafting</span>
                                    <button className="">
                                        <ChevronRight className="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div className="p-6">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center space-x-4">
                                    <div className="flex h-12 w-12 items-center justify-center rounded-lg">
                                        <Building className="h-5 w-5 " />
                                    </div>
                                    <div>
                                        <h3 className="">Office League 2025</h3>
                                        <p className="text-sm">8 teams • Half PPR • Snake Draft</p>
                                        <p className="text-sm">Draft: March 25, 2025 at 6:30 PM</p>
                                    </div>
                                </div>
                                <div className="flex items-center space-x-4">
                                    <span className="rounded-full px-3 py-1 text-sm">Scheduled</span>
                                    <button className="">
                                        <ChevronRight className="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div className="p-6">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center space-x-4">
                                    <div className="flex h-12 w-12 items-center justify-center rounded-lg">
                                        <Trophy className="h-5 w-5 " />
                                    </div>
                                    <div>
                                        <h3 className="">Dynasty League 2024</h3>
                                        <p className="text-sm">12 teams • PPR • Dynasty Format</p>
                                        <p className="text-sm">Season completed: January 15, 2025</p>
                                    </div>
                                </div>
                                <div className="flex items-center space-x-4">
                                    <span className="rounded-full px-3 py-1 text-sm">Completed</span>
                                    <button className="">
                                        <ChevronRight className="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
