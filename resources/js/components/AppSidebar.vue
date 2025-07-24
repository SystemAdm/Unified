<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    CalendarIcon,
    Folder,
    GamepadIcon,
    LayoutGrid,
    Lock,
    Mail,
    MapPin,
    Megaphone,
    Newspaper,
    Phone,
    ShieldCheck,
    Speaker,
    UserCog,
    Users
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user || null);
const userRoles = computed(() => user.value?.roles || []);

// Check if user has admin privileges (ADMIN, MODERATOR, or OWNER role)
const isAdmin = computed(() => {
    return userRoles.value.some((role) => ['admin', 'moderator', 'owner'].includes(role));
});

const mainNavItems = computed(() => {
    // Items visible to all users
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: route('dashboard'),
            icon: LayoutGrid,
        },
        {
            title: 'Events',
            href: route('events.index'),
            icon: CalendarIcon,
        },
        {
            title: 'Locations',
            href: route('locations.index'),
            icon: MapPin,
        },
        {
            title: 'Organizations',
            href: route('organizations.index'),
            icon: Users,
        },
    ];

    // Items only visible to admin users
    if (isAdmin.value) {
        items.push(
            {
              title: '-----------',
              href:'',
            },
            {
                title: 'Admin Dashboard',
                href: route('admin.index'),
                icon: LayoutGrid,
            },
            {
                title: 'Organizations',
                href: route('admin.organizations.index'),
                icon: Users,
            },
            {
                title:'Events',
                href: route('admin.events.index'),
                icon: CalendarIcon,
            },
            {
                title: 'Users',
                href: route('admin.users.index'),
                icon: UserCog,
            },
            {
                title: 'Phones',
                href: route('admin.phones.index'),
                icon: Phone,
            },
            {
                title: 'Emails',
                href: route('admin.emails.index'),
                icon: Mail,
            },
            {
                title: 'Locations',
                href: route('admin.locations.index'),
                icon: MapPin,
            },
            {
                title: 'Roles',
                href: route('admin.roles.index'),
                icon: ShieldCheck,
            },
            {
                title: 'Permissions',
                href: route('admin.permissions.index'),
                icon: Lock,
            },
            {
                title:'Games', href: route('admin.games.index'), icon: GamepadIcon,
            },
            {
                title: 'Banners',
                href: route('admin.banners.index'),
                icon: Speaker,
            },
            {
                title: 'Announcements',
                href: route('admin.announcements.index'),
                icon: Megaphone,
            },
            {
                title: 'News',
                href: route('admin.news.index'),
                icon: Newspaper,
            }
        );
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('home')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
