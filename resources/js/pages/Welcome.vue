<script setup lang="ts">
// Define Cloudflare Turnstile types
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faDiscord } from '@fortawesome/free-brands-svg-icons';

declare global {
    interface Window {
        turnstile: {
            render: (container: string | HTMLElement, options: any) => string;
            reset: (widgetId: string) => void;
            getResponse: (widgetId: string) => string;
            remove: (widgetId: string) => void;
        };
    }
}

import { Head, Link } from '@inertiajs/vue3';
import { ref, reactive, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

// UI Components
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';

// Icons
import { Calendar, ShoppingCart, Info, Users, Heart, Mail, DollarSign, MapPin as MapPinIcon, LogInIcon, ClipboardPenLineIcon, Bell, Megaphone, Newspaper

} from 'lucide-vue-next';

// Define props
defineProps<{
    games: {
        id: number;
        name: string;
        version: string;
        console: string;
        image: string | null;
        is_active: boolean;
        created_at: string;
        updated_at: string;
    }[];
    events: {
        id: number;
        title: string;
        description: string;
        start_date: string;
        end_date: string;
        location: {
            id: number;
            name: string;
            address: string;
        } | null;
    }[];
    banners: {
        id: number;
        title: string;
        description: string;
        type: string;
        from_datetime: string;
        to_datetime: string;
        link_norwegian: string | null;
        link_english: string | null;
        activating: boolean;
    }[];
    announcements: {
        id: number;
        title: string;
        description: string;
        type: string;
        from_datetime: string;
        to_datetime: string;
        activating: boolean;
    }[];
    news: {
        id: number;
        title: string;
        excerpt: string | null;
        content: string;
        author: string | null;
        featured_image: string | null;
        published_at: string | null;
        is_published: boolean;
        created_at: string;
        updated_at: string;
    }[];
}>();

// Format date for display
const formatEventDate = (date: string) => {
    const eventDate = new Date(date);
    return eventDate.toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
};

// Format time for display
const formatEventTime = (date: string) => {
    const eventDate = new Date(date);
    return eventDate.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

// Contact form
const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const formStatus = reactive({
    success: false,
    error: false,
    message: '',
});

// Load Cloudflare Turnstile script
onMounted(() => {});

const submitContactForm = () => {};
// Mock data for gaming consoles
const gamingConsoles = ref([
    {
        id: 1,
        name: 'PlayStation 5',
        description:
            'Experience lightning-fast loading with an ultra-high speed SSD, deeper immersion with support for haptic feedback, adaptive triggers, and 3D Audio.',
        price: '$499.99',
        image: 'https://placehold.co/400x200/0f0f0f/ffffff?text=PS5',
    },
    {
        id: 2,
        name: 'Xbox Series X',
        description:
            'The most powerful Xbox ever, designed for a console generation that has you at its center with 12 teraflops of processing power.',
        price: '$499.99',
        image: 'https://placehold.co/400x200/0f0f0f/ffffff?text=Xbox',
    },
    {
        id: 3,
        name: 'Nintendo Switch',
        description: 'The Nintendo Switch is designed to fit your life, transforming from home console to portable system in a snap.',
        price: '$299.99',
        image: 'https://placehold.co/400x200/0f0f0f/ffffff?text=Switch',
    },
]);

// Membership tiers
// Note: we only have one membership tier
const membershipTiers = ref([
    {
        id: 1,
        name: 'Membership',
        price: '50 NOK/year',
        features: ['Access to community events', 'Game discounts', 'Online forum access', 'Priority event registration'],
    },
]);
</script>

<template>
    <div class="min-h-screen bg-background text-foreground">
        <Head title="Gaming Hub - Home" />

        <!-- Scrolling Banner Section -->
        <section v-if="banners.length > 0" class="overflow-hidden bg-primary/10 py-2 relative">
            <div class="scrolling-banner-container">
                <div class="scrolling-banner">
                    <div v-for="banner in banners" :key="banner.id" class="scrolling-banner-item px-4 py-1 mx-2 rounded-md" :class="{
                        'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800': banner.type === 'warning',
                        'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800': banner.type === 'danger',
                        'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800': banner.type === 'info',
                        'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800': banner.type === 'primary',
                        'bg-gray-50 border-gray-200 dark:bg-gray-900/20 dark:border-gray-800': banner.type === 'secondary' || banner.type === 'default'
                    }">
                        <div class="flex items-center space-x-2">
                            <Bell class="h-4 w-4 flex-shrink-0" :class="{
                                'text-yellow-600 dark:text-yellow-400': banner.type === 'warning',
                                'text-red-600 dark:text-red-400': banner.type === 'danger',
                                'text-blue-600 dark:text-blue-400': banner.type === 'info',
                                'text-green-600 dark:text-green-400': banner.type === 'primary',
                                'text-gray-600 dark:text-gray-400': banner.type === 'secondary' || banner.type === 'default'
                            }" />
                            <span class="font-medium">{{ banner.title }}:</span>
                            <span>{{ banner.description }}</span>
                            <div v-if="banner.link_norwegian || banner.link_english" class="ml-2">
                                <a v-if="banner.link_norwegian" :href="banner.link_norwegian" target="_blank" class="text-primary hover:underline mr-2">
                                    Les mer (NO)
                                </a>
                                <a v-if="banner.link_english" :href="banner.link_english" target="_blank" class="text-primary hover:underline">
                                    Read more (EN)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hero Section -->
        <section class="relative flex h-[70vh] items-center justify-center overflow-hidden">
            <div class="absolute inset-0 z-10 bg-gradient-to-r from-background/90 to-muted/90"></div>
            <div class="absolute inset-0 bg-[url('https://placehold.co/1920x1080/0f0f0f/ffffff?text=Gaming+Background')] bg-cover bg-center"></div>
            <div class="relative z-20 container mx-auto px-4 text-center">
                <h1 class="mb-4 text-5xl font-bold md:text-7xl">Gaming Hub</h1>
                <p class="mx-auto mb-8 max-w-3xl text-xl text-muted-foreground md:text-2xl">
                    Your ultimate destination for gaming events, latest releases, and community gatherings
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <Link :href="route('login')"><Button size="lg" variant="default"><LogInIcon /> Sign in / <ClipboardPenLineIcon /> Register an account </Button></Link>
                    <a href="https://discord.gg/k6WDYMx" target="_blank">
                        <Button size="lg" variant="secondary">
                            <font-awesome-icon :icon="faDiscord" />
                            Join our Discord Community
                        </Button>
                    </a>
                </div>
            </div>
        </section>

        <!-- Upcoming Events Section -->
        <section class="bg-muted py-16">
            <div class="container mx-auto px-4">
                <div class="mb-10 flex items-center">
                    <Calendar class="mr-3 h-8 w-8 text-muted-foreground" />
                    <h2 class="text-3xl font-bold">Upcoming Events</h2>
                </div>
                <div v-if="events.length === 0" class="p-4 text-center text-gray-500">No upcoming events found.</div>
                <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="event in events" :key="event.id" class="overflow-hidden">
                        <img
                            :src="`https://placehold.co/300x200/0f0f0f/ffffff?text=${encodeURIComponent(event.title)}`"
                            :alt="event.title"
                            class="h-48 w-full object-cover"
                        />
                        <CardHeader>
                            <CardTitle>{{ event.title }}</CardTitle>
                            <CardDescription> {{ formatEventDate(event.start_date) }} at {{ formatEventTime(event.start_date) }} </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="event.location" class="mb-2 flex items-center text-gray-500 dark:text-gray-400">
                                <MapPinIcon class="mr-2 h-4 w-4" />
                                <span>{{ event.location.name }}</span>
                            </div>
                            <p class="text-muted-foreground">{{ event.description }}</p>
                        </CardContent>
                        <CardFooter>
                            <Link :href="route('events.show', { event: event.id })" class="w-full">
                                <Button class="w-full">View Details</Button>
                            </Link>
                        </CardFooter>
                    </Card>
                </div>
                <div class="mt-10 text-center">
                    <Link :href="route('events.index')">
                        <Button variant="outline"> View All Events </Button>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Announcements Section -->
        <section v-if="announcements.length > 0" class="bg-background py-16">
            <div class="container mx-auto px-4">
                <div class="mb-10 flex items-center">
                    <Megaphone class="mr-3 h-8 w-8 text-muted-foreground" />
                    <h2 class="text-3xl font-bold">Announcements</h2>
                </div>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="announcement in announcements" :key="announcement.id" class="overflow-hidden">
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-lg">{{ announcement.title }}</CardTitle>
                                <Badge :variant="announcement.type === 'warning' ? 'destructive' :
                                               announcement.type === 'danger' ? 'destructive' :
                                               announcement.type === 'info' ? 'secondary' :
                                               announcement.type === 'primary' ? 'default' : 'outline'">
                                    {{ announcement.type.toUpperCase() }}
                                </Badge>
                            </div>
                            <CardDescription>
                                {{ formatEventDate(announcement.from_datetime) }}
                                <span v-if="announcement.to_datetime !== announcement.from_datetime">
                                    - {{ formatEventDate(announcement.to_datetime) }}
                                </span>
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground">{{ announcement.description.substring(0, 150) + (announcement.description.length > 150 ? '...' : '') }}</p>
                        </CardContent>
                        <CardFooter>
                            <Link :href="route('announcements.show', { announcement: announcement.id })">
                                <Button class="w-full">Read More</Button>
                            </Link>
                        </CardFooter>
                    </Card>
                </div>
                <div class="mt-10 text-center">
                    <Link :href="route('announcements.index')">
                        <Button variant="outline">View All Announcements</Button>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Games Section -->
        <section class="bg-background py-16">
            <div class="container mx-auto px-4">
                <div class="mb-10 flex items-center">
                    <ShoppingCart class="mr-3 h-8 w-8 text-muted-foreground" />
                    <h2 class="text-3xl font-bold">Our Games</h2>
                </div>
                <div class="grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-4">
                    <Card v-for="game in games" :key="game.id">
                        <img
                            :src="
                                game.image
                                    ? `/storage/${game.image}`
                                    : `https://placehold.co/200x300/0f0f0f/ffffff?text=${encodeURIComponent(game.name)}`
                            "
                            :alt="game.name"
                            class="h-64 w-full object-cover"
                        />
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-lg">{{ game.name }}</CardTitle>
                                <Badge class="font-semibold" v-if="game.version">ver.: {{ game.version }}</Badge>
                            </div>
                            <Badge>{{ game.console }}</Badge>
                        </CardHeader>
                        <CardFooter>
                            <Link :href="route('games.show', { game: game.id })" class="w-full">
                                <Button class="w-full">View Details</Button>
                            </Link>
                        </CardFooter>
                    </Card>
                </div>
            </div>
            <div class="mt-10 flex justify-center">
                <Link href="/games">Show more...</Link>
            </div>
        </section>

        <!-- Gaming Consoles Section -->
        <section class="bg-muted py-16">
            <div class="container mx-auto px-4">
                <div class="mb-10 flex items-center">
                    <ShoppingCart class="mr-3 h-8 w-8 text-muted-foreground" />
                    <h2 class="text-3xl font-bold">Gaming Consoles</h2>
                </div>
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="console in gamingConsoles" :key="console.id">
                        <img :src="console.image" :alt="console.name" class="h-48 w-full object-cover" />
                        <CardHeader>
                            <CardTitle>{{ console.name }}</CardTitle>
                            <CardDescription class="font-semibold">
                                {{ console.price }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground">{{ console.description }}</p>
                        </CardContent>
                        <CardFooter>
                            <Button class="w-full">Learn More</Button>
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </section>

        <!-- News Section -->
        <section v-if="news.length > 0" class="bg-background py-16">
            <div class="container mx-auto px-4">
                <div class="mb-10 flex items-center">
                    <Newspaper class="mr-3 h-8 w-8 text-muted-foreground" />
                    <h2 class="text-3xl font-bold">Latest News</h2>
                </div>
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="article in news" :key="article.id" class="overflow-hidden">
                        <img
                            :src="article.featured_image
                                ? `/storage/${article.featured_image}`
                                : `https://placehold.co/400x200/0f0f0f/ffffff?text=${encodeURIComponent(article.title)}`"
                            :alt="article.title"
                            class="h-48 w-full object-cover"
                        />
                        <CardHeader>
                            <CardTitle class="text-lg">{{ article.title }}</CardTitle>
                            <CardDescription>
                                <span v-if="article.author">By {{ article.author }} • </span>
                                {{ article.published_at ? formatEventDate(article.published_at) : formatEventDate(article.created_at) }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground">
                                {{ article.excerpt || article.content.substring(0, 150) + '...' }}
                            </p>
                        </CardContent>
                        <CardFooter>
                            <Link :href="route('news.show', { news: article.id })">
                                <Button class="w-full">Read More</Button>
                            </Link>
                        </CardFooter>
                    </Card>
                </div>
                <div class="mt-10 text-center">
                    <Link :href="route('news.index')">
                        <Button variant="outline">View All News</Button>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Membership Section -->
        <section class="bg-background py-16">
            <div class="container mx-auto px-4">
                <div class="mb-10 flex items-center">
                    <Users class="mr-3 h-8 w-8 text-muted-foreground" />
                    <h2 class="text-3xl font-bold">Membership</h2>
                </div>
                <div class="flex justify-center">
                    <Card v-for="tier in membershipTiers" :key="tier.id" class="transition-transform hover:scale-105 max-w-md w-full">
                        <CardHeader class="text-center">
                            <CardTitle class="text-2xl">{{ tier.name }}</CardTitle>
                            <CardDescription class="text-xl font-bold">
                                {{ tier.price }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ul class="space-y-2">
                                <li v-for="(feature, index) in tier.features" :key="index" class="flex items-start">
                                    <span class="mr-2">✓</span>
                                    <span>{{ feature }}</span>
                                </li>
                            </ul>
                        </CardContent>
                        <CardFooter>
                            <Button class="w-full">Join Now</Button>
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </section>

        <!-- About Us Section -->
        <section class="bg-muted py-16">
            <div class="container mx-auto px-4">
                <div class="mb-10 flex items-center">
                    <Info class="mr-3 h-8 w-8 text-muted-foreground" />
                    <h2 class="text-3xl font-bold">About Us</h2>
                </div>
                <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2">
                    <div>
                        <img
                            src="https://placehold.co/600x400/0f0f0f/ffffff?text=Gaming+Community"
                            alt="Our Gaming Community"
                            class="rounded-lg shadow-lg"
                        />
                    </div>
                    <div class="space-y-6">
                        <h3 class="text-2xl font-semibold">Our Mission</h3>
                        <p class="text-muted-foreground">
                            At Gaming Hub, we're passionate about creating a vibrant community where gamers of all levels can connect, compete, and
                            celebrate their love for gaming. Founded in 2015, we've grown from a small local group to a thriving community with
                            members from around the world.
                        </p>
                        <p class="text-muted-foreground">
                            Our mission is to provide a welcoming space for gamers to share experiences, improve their skills, and form lasting
                            friendships. We believe that gaming is more than just a hobby—it's a way to connect, learn, and grow together.
                        </p>
                        <Button>Learn More About Us</Button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="bg-background py-16">
            <div class="container mx-auto px-4">
                <div class="mb-10 flex items-center">
                    <Mail class="mr-3 h-8 w-8 text-muted-foreground" />
                    <h2 class="text-3xl font-bold">Get In Touch</h2>
                </div>
                <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Send Us a Message</CardTitle>
                            <CardDescription>
                                We'd love to hear from you! Fill out the form and we'll get back to you as soon as possible.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="formStatus.success"
                                class="mb-4 rounded-md bg-green-100 p-4 text-green-800 dark:bg-green-900 dark:text-green-100"
                            >
                                {{ formStatus.message }}
                            </div>
                            <div v-if="formStatus.error" class="mb-4 rounded-md bg-red-100 p-4 text-red-800 dark:bg-red-900 dark:text-red-100">
                                {{ formStatus.message }}
                            </div>
                            <form @submit.prevent="submitContactForm" class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <label for="name" class="text-sm font-medium">Name</label>
                                        <Input
                                            id="name"
                                            v-model="form.name"
                                            placeholder="Your name"
                                            :class="{ 'border-red-500': form.errors.name }"
                                            required
                                        />
                                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                                            {{ form.errors.name }}
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="email" class="text-sm font-medium">Email</label>
                                        <Input
                                            id="email"
                                            type="email"
                                            v-model="form.email"
                                            placeholder="Your email"
                                            :class="{ 'border-red-500': form.errors.email }"
                                            required
                                        />
                                        <div v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                                            {{ form.errors.email }}
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label for="subject" class="text-sm font-medium">Subject</label>
                                    <Input
                                        id="subject"
                                        v-model="form.subject"
                                        placeholder="Message subject"
                                        :class="{ 'border-red-500': form.errors.subject }"
                                        required
                                    />
                                    <div v-if="form.errors.subject" class="mt-1 text-sm text-red-500">
                                        {{ form.errors.subject }}
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label for="message" class="text-sm font-medium">Message</label>
                                    <Textarea
                                        id="message"
                                        v-model="form.message"
                                        placeholder="Your message"
                                        rows="5"
                                        :class="{ 'border-red-500': form.errors.message }"
                                        required
                                    />
                                    <div v-if="form.errors.message" class="mt-1 text-sm text-red-500">
                                        {{ form.errors.message }}
                                    </div>
                                </div>

                                <Button type="submit" class="w-full" :disabled="form.processing">
                                    {{ form.processing ? 'Sending...' : 'Send Message' }}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                    <div class="space-y-8">
                        <div>
                            <h3 class="mb-4 text-xl font-semibold">Visit Our Gaming Center</h3>
                            <p class="mb-2 text-muted-foreground">123 Gamer Street</p>
                            <p class="mb-2 text-muted-foreground">Pixel City, PC 12345</p>
                            <p class="text-muted-foreground">Open daily: 10AM - 10PM</p>
                        </div>
                        <div>
                            <h3 class="mb-4 text-xl font-semibold">Contact Information</h3>
                            <p class="mb-2 text-muted-foreground">Email: info@gaminghub.com</p>
                            <p class="mb-2 text-muted-foreground">Phone: (555) 123-4567</p>
                            <p class="text-muted-foreground">Discord: GamingHub#1234</p>
                        </div>
                        <div>
                            <h3 class="mb-4 text-xl font-semibold">Follow Us</h3>
                            <div class="flex space-x-4">
                                <a href="#" class="text-muted-foreground transition-colors hover:text-foreground">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path
                                            fill-rule="evenodd"
                                            d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </a>
                                <a href="#" class="text-muted-foreground transition-colors hover:text-foreground">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path
                                            d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"
                                        />
                                    </svg>
                                </a>
                                <a href="#" class="text-muted-foreground transition-colors hover:text-foreground">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path
                                            fill-rule="evenodd"
                                            d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </a>
                                <a href="#" class="text-muted-foreground transition-colors hover:text-foreground">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path
                                            d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.007 2.007 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.007 2.007 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31.4 31.4 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.007 2.007 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A99.788 99.788 0 0 1 7.858 2h.193zM6.4 5.209v4.818l4.157-2.408L6.4 5.209z"
                                        />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Support Section -->
        <section class="bg-muted py-16">
            <div class="container mx-auto px-4">
                <div class="mb-10 flex items-center">
                    <Heart class="mr-3 h-8 w-8 text-muted-foreground" />
                    <h2 class="text-3xl font-bold">Support Our Work</h2>
                </div>
                <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2">
                    <div class="space-y-6">
                        <h3 class="text-2xl font-semibold">Help Us Grow</h3>
                        <p class="text-muted-foreground">
                            Your support helps us continue to provide quality gaming experiences and events for our community. There are many ways you
                            can contribute to our mission:
                        </p>
                        <ul class="space-y-4">
                            <li class="flex items-start">
                                <DollarSign class="mt-0.5 mr-2 h-6 w-6 flex-shrink-0" />
                                <div>
                                    <h4 class="font-medium">Become a Member</h4>
                                    <p class="text-muted-foreground">
                                        Join our membership and enjoy exclusive benefits while supporting our community.
                                    </p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <DollarSign class="mt-0.5 mr-2 h-6 w-6 flex-shrink-0" />
                                <div>
                                    <h4 class="font-medium">Donate</h4>
                                    <p class="text-muted-foreground">
                                        Make a one-time donation to help fund new equipment, events, and community initiatives.
                                    </p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <DollarSign class="mt-0.5 mr-2 h-6 w-6 flex-shrink-0" />
                                <div>
                                    <h4 class="font-medium">Volunteer</h4>
                                    <p class="text-muted-foreground">
                                        Share your time and skills by volunteering at our events or helping with community management.
                                    </p>
                                </div>
                            </li>
                        </ul>
                        <Button>Support Now</Button>
                    </div>
                    <div>
                        <Card>
                            <CardHeader>
                                <CardTitle>Make a Donation</CardTitle>
                                <CardDescription> Every contribution helps us create better gaming experiences </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form class="space-y-4">
                                    <div class="space-y-2">
                                        <label for="amount" class="text-sm font-medium">Donation Amount</label>
                                        <div class="grid grid-cols-4 gap-2">
                                            <Button variant="outline">$5</Button>
                                            <Button variant="outline">$10</Button>
                                            <Button variant="outline">$25</Button>
                                            <Button variant="outline">$50</Button>
                                        </div>
                                        <Input id="custom-amount" type="number" placeholder="Custom amount" class="mt-2" />
                                    </div>
                                    <div class="space-y-2">
                                        <label for="message" class="text-sm font-medium">Message (Optional)</label>
                                        <Textarea id="message" placeholder="Your message" rows="3" />
                                    </div>
                                    <Button type="submit" class="w-full">Donate Now</Button>
                                </form>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t bg-background py-10">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <h3 class="mb-4 text-xl font-bold">Gaming Hub</h3>
                        <p class="text-muted-foreground">Your ultimate gaming community destination since 2015.</p>
                    </div>
                    <div>
                        <h3 class="mb-4 text-lg font-semibold">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-muted-foreground transition-colors hover:text-foreground">Events</a></li>
                            <li><a href="#" class="text-muted-foreground transition-colors hover:text-foreground">Games</a></li>
                            <li><a href="#" class="text-muted-foreground transition-colors hover:text-foreground">Membership</a></li>
                            <li><a href="#" class="text-muted-foreground transition-colors hover:text-foreground">About Us</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-4 text-lg font-semibold">Legal</h3>
                        <ul class="space-y-2">
                            <li><Link :href="route('legal.tos')" target="_blank" class="text-muted-foreground transition-colors hover:text-foreground">Terms of Service</Link></li>
                            <li><Link :href="route('legal.privacy')" target="_blank" class="text-muted-foreground transition-colors hover:text-foreground">Privacy Policy</Link></li>
                            <li><Link :href="route('legal.cookie')" target="_blank" class="text-muted-foreground transition-colors hover:text-foreground">Cookie Policy</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-4 text-lg font-semibold">Newsletter</h3>
                        <p class="mb-4 text-muted-foreground">Subscribe to get updates on events and promotions.</p>
                        <div class="flex">
                            <Input placeholder="Your email" class="rounded-r-none" />
                            <Button class="rounded-l-none">Subscribe</Button>
                        </div>
                    </div>
                </div>
                <div class="mt-8 border-t pt-8 text-center text-muted-foreground">
                    <p>&copy; 2023 Gaming Hub. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Additional custom styles can be added here */

/* Scrolling Banner Styles */
.scrolling-banner-container {
  width: 100%;
  overflow: hidden;
  position: relative;
}

.scrolling-banner {
  display: flex;
  white-space: nowrap;
  animation: scrollBanner 30s linear infinite;
}

.scrolling-banner-item {
  display: inline-flex;
  border: 1px solid;
  align-items: center;
  flex-shrink: 0; /* Prevent items from shrinking */
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .scrolling-banner {
    animation-duration: 20s; /* Faster on mobile */
  }

  .scrolling-banner-item {
    font-size: 0.875rem; /* Smaller font on mobile */
  }
}

@keyframes scrollBanner {
  0% {
    transform: translateX(100%);
  }
  100% {
    transform: translateX(-100%);
  }
}

/* Pause animation on hover */
.scrolling-banner:hover {
  animation-play-state: paused;
}
</style>
