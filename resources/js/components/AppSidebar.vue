<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, CalendarIcon, Folder, LayoutGrid, Lock, Mail, MapPin, Phone, ShieldCheck, UserCog, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
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
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Events',
            href: '/events',
            icon: CalendarIcon,
        },
        {
            title: 'Locations',
            href: '/locations',
            icon: MapPin,
        },
        {
            title: 'Organizations',
            href: '/organizations',
            icon: Users,
        },
    ];

    // Items only visible to admin users
    if (isAdmin.value) {
        items.push(

            {
                title: 'Admin Dashboard',
                href: '/admin',
                icon: LayoutGrid,
            },
            {
                title: 'Admin Organizations',
                href: '/admin/organizations',
                icon: Users,
            },
            {
                title:'Admin Events',
                href: '/admin/events',
                icon: CalendarIcon,
            },
            {
                title: 'Users',
                href: '/admin/users',
                icon: UserCog,
            },
            {
                title: 'Phones',
                href: '/admin/phones',
                icon: Phone,
            },
            {
                title: 'Emails',
                href: '/admin/emails',
                icon: Mail,
            },
            {
                title: 'Locations',
                href: '/admin/locations',
                icon: MapPin,
            },
            {
                title: 'Roles',
                href: '/admin/roles',
                icon: ShieldCheck,
            },
            {
                title: 'Permissions',
                href: '/admin/permissions',
                icon: Lock,
            },
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
                        <Link :href="route('dashboard')">
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
