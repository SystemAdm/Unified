<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { CookieIcon } from 'lucide-vue-next';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: route('home') },
    { title: 'Cookie Policy', href: route('legal.cookie') },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Cookie Policy" />

        <div class="mb-10 flex items-center">
            <CookieIcon class="mr-3 h-8 w-8 text-muted-foreground" />
            <h2 class="text-3xl font-bold">Cookie Policy</h2>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="text-2xl">Cookie Policy</CardTitle>
                <CardDescription>Last Updated: {{ new Date().toLocaleDateString() }}</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold">1. Introduction</h3>
                        <p class="mt-2 text-muted-foreground">
                            This Cookie Policy explains how Gaming Hub uses cookies and similar technologies to recognize you when you visit our website.
                            It explains what these technologies are and why we use them, as well as your rights to control our use of them.
                            Please also review our <Link :href="route('legal.privacy')" target="_blank" class="underline hover:text-foreground">Privacy Policy</Link> and
                            <Link :href="route('legal.tos')" target="_blank" class="underline hover:text-foreground">Terms of Service</Link> for more information about how we use your personal information.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">2. What Are Cookies?</h3>
                        <p class="mt-2 text-muted-foreground">
                            Cookies are small data files that are placed on your computer or mobile device when you visit a website.
                            Cookies are widely used by website owners to make their websites work, or to work more efficiently,
                            as well as to provide reporting information.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            Cookies set by the website owner (in this case, Gaming Hub) are called "first-party cookies".
                            Cookies set by parties other than the website owner are called "third-party cookies".
                            Third-party cookies enable third-party features or functionality to be provided on or through the website
                            (e.g., advertising, interactive content, and analytics).
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">3. Types of Cookies We Use</h3>
                        <p class="mt-2 text-muted-foreground">
                            We use the following types of cookies:
                        </p>
                        <ul class="ml-6 mt-2 list-disc text-muted-foreground">
                            <li class="mt-1">
                                <strong>Essential Cookies:</strong> These cookies are necessary for the website to function and cannot be switched off in our systems.
                                They are usually only set in response to actions made by you which amount to a request for services,
                                such as setting your privacy preferences, logging in, or filling in forms.
                            </li>
                            <li class="mt-1">
                                <strong>Performance Cookies:</strong> These cookies allow us to count visits and traffic sources so we can measure and improve the performance of our site.
                                They help us to know which pages are the most and least popular and see how visitors move around the site.
                            </li>
                            <li class="mt-1">
                                <strong>Functionality Cookies:</strong> These cookies enable the website to provide enhanced functionality and personalization.
                                They may be set by us or by third-party providers whose services we have added to our pages.
                            </li>
                            <li class="mt-1">
                                <strong>Targeting Cookies:</strong> These cookies may be set through our site by our advertising partners.
                                They may be used by those companies to build a profile of your interests and show you relevant advertisements on other sites.
                            </li>
                        </ul>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">4. How Long Do Cookies Stay on My Device?</h3>
                        <p class="mt-2 text-muted-foreground">
                            The length of time a cookie will remain on your device depends on whether it is a "persistent" or "session" cookie.
                            Session cookies will remain on your device until you stop browsing.
                            Persistent cookies remain on your device until they expire or are deleted.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">5. How to Control and Delete Cookies</h3>
                        <p class="mt-2 text-muted-foreground">
                            Most web browsers allow some control of most cookies through the browser settings.
                            To find out more about cookies, including how to see what cookies have been set,
                            visit <a href="https://www.allaboutcookies.org" target="_blank" class="underline hover:text-foreground">www.allaboutcookies.org</a>.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            You can set or amend your web browser controls to accept or refuse cookies. If you choose to reject cookies,
                            you may still use our website though your access to some functionality and areas of our website may be restricted.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">6. Specific Browser Instructions</h3>
                        <p class="mt-2 text-muted-foreground">
                            Here's how to control cookies in the most popular browsers:
                        </p>
                        <ul class="ml-6 mt-2 list-disc text-muted-foreground">
                            <li class="mt-1">
                                <strong>Chrome:</strong> Settings → Privacy and security → Cookies and other site data
                            </li>
                            <li class="mt-1">
                                <strong>Firefox:</strong> Options → Privacy & Security → Cookies and Site Data
                            </li>
                            <li class="mt-1">
                                <strong>Safari:</strong> Preferences → Privacy → Cookies and website data
                            </li>
                            <li class="mt-1">
                                <strong>Edge:</strong> Settings → Cookies and site permissions → Cookies and site data
                            </li>
                        </ul>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">7. Third-Party Cookies</h3>
                        <p class="mt-2 text-muted-foreground">
                            We may use third-party services that use cookies on our website, including:
                        </p>
                        <ul class="ml-6 mt-2 list-disc text-muted-foreground">
                            <li class="mt-1">Google Analytics (for website analytics)</li>
                            <li class="mt-1">Social media plugins (for sharing content)</li>
                            <li class="mt-1">Payment processors (for processing transactions)</li>
                            <li class="mt-1">Advertising networks (for displaying relevant ads)</li>
                        </ul>
                        <p class="mt-2 text-muted-foreground">
                            These third parties may have their own privacy policies and cookie policies which we encourage you to review.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">8. Changes to This Cookie Policy</h3>
                        <p class="mt-2 text-muted-foreground">
                            We may update our Cookie Policy from time to time. We will notify you of any changes by posting
                            the new Cookie Policy on this page and updating the "Last Updated" date. You are advised to review
                            this Cookie Policy periodically for any changes.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">9. Contact Us</h3>
                        <p class="mt-2 text-muted-foreground">
                            If you have any questions about our use of cookies or this Cookie Policy, please contact us at:
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            Email: privacy@gaminghub.com<br>
                            Phone: (555) 123-4567<br>
                            Address: 123 Gamer Street, Pixel City, PC 12345
                        </p>
                    </div>
                </div>
            </CardContent>
            <CardFooter>
                <p class="text-sm text-muted-foreground">
                    By continuing to use our website, you acknowledge that you have read and understood this Cookie Policy.
                </p>
            </CardFooter>
        </Card>
    </AppLayout>
</template>
