<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ShieldIcon } from 'lucide-vue-next';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: route('home') },
    { title: 'Privacy Policy', href: route('legal.privacy') },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Privacy Policy" />

        <div class="mb-10 flex items-center">
            <ShieldIcon class="mr-3 h-8 w-8 text-muted-foreground" />
            <h2 class="text-3xl font-bold">Privacy Policy</h2>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="text-2xl">Privacy Policy</CardTitle>
                <CardDescription>Last Updated: {{ new Date().toLocaleDateString() }}</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold">1. Introduction</h3>
                        <p class="mt-2 text-muted-foreground">
                            At Gaming Hub, we respect your privacy and are committed to protecting your personal data.
                            This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our service.
                            Please also review our <Link :href="route('legal.tos')" target="_blank" class="underline hover:text-foreground">Terms of Service</Link> for information about your rights and responsibilities when using our platform.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">2. Information We Collect</h3>
                        <p class="mt-2 text-muted-foreground">
                            We collect several types of information from and about users of our service, including:
                        </p>
                        <ul class="ml-6 mt-2 list-disc text-muted-foreground">
                            <li class="mt-1">Personal identifiers such as name, email address, and phone number</li>
                            <li class="mt-1">Account credentials</li>
                            <li class="mt-1">Profile information</li>
                            <li class="mt-1">Usage data and activity information</li>
                            <li class="mt-1">Device and connection information</li>
                        </ul>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">3. How We Collect Information</h3>
                        <p class="mt-2 text-muted-foreground">
                            We collect information directly from you when you:
                        </p>
                        <ul class="ml-6 mt-2 list-disc text-muted-foreground">
                            <li class="mt-1">Register for an account</li>
                            <li class="mt-1">Complete your profile</li>
                            <li class="mt-1">Participate in events or activities</li>
                            <li class="mt-1">Contact us or submit inquiries</li>
                            <li class="mt-1">Use our services</li>
                        </ul>
                        <p class="mt-2 text-muted-foreground">
                            We also collect information automatically through cookies and similar technologies when you use our service.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">4. How We Use Your Information</h3>
                        <p class="mt-2 text-muted-foreground">
                            We use the information we collect to:
                        </p>
                        <ul class="ml-6 mt-2 list-disc text-muted-foreground">
                            <li class="mt-1">Provide, maintain, and improve our services</li>
                            <li class="mt-1">Process transactions and manage your account</li>
                            <li class="mt-1">Communicate with you about events, updates, and other information</li>
                            <li class="mt-1">Personalize your experience</li>
                            <li class="mt-1">Analyze usage patterns and improve our service</li>
                            <li class="mt-1">Protect against unauthorized access and ensure the security of our service</li>
                        </ul>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">5. Information Sharing and Disclosure</h3>
                        <p class="mt-2 text-muted-foreground">
                            We may share your information with:
                        </p>
                        <ul class="ml-6 mt-2 list-disc text-muted-foreground">
                            <li class="mt-1">Service providers who perform services on our behalf</li>
                            <li class="mt-1">Event organizers when you register for events</li>
                            <li class="mt-1">Other users, as part of the community features of our service</li>
                            <li class="mt-1">Legal authorities when required by law</li>
                        </ul>
                        <p class="mt-2 text-muted-foreground">
                            We do not sell your personal information to third parties.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">6. Data Security</h3>
                        <p class="mt-2 text-muted-foreground">
                            We implement appropriate technical and organizational measures to protect your personal information
                            from unauthorized access, disclosure, alteration, and destruction. However, no method of transmission
                            over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">7. Cookies and Tracking Technologies</h3>
                        <p class="mt-2 text-muted-foreground">
                            We use cookies and similar tracking technologies to track activity on our service and hold certain information.
                            Cookies are files with a small amount of data that may include an anonymous unique identifier.
                            You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">8. Your Privacy Rights</h3>
                        <p class="mt-2 text-muted-foreground">
                            Depending on your location, you may have certain rights regarding your personal information, including:
                        </p>
                        <ul class="ml-6 mt-2 list-disc text-muted-foreground">
                            <li class="mt-1">Right to access your personal information</li>
                            <li class="mt-1">Right to correct inaccurate information</li>
                            <li class="mt-1">Right to delete your personal information</li>
                            <li class="mt-1">Right to restrict processing</li>
                            <li class="mt-1">Right to data portability</li>
                            <li class="mt-1">Right to object to processing</li>
                        </ul>
                        <p class="mt-2 text-muted-foreground">
                            To exercise these rights, please contact us using the information provided in the "Contact Us" section.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">9. Children's Privacy</h3>
                        <p class="mt-2 text-muted-foreground">
                            Our service is not intended for children under 13 years of age. We do not knowingly collect personal
                            information from children under 13. If you are a parent or guardian and believe your child has provided
                            us with personal information, please contact us.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">10. Changes to This Privacy Policy</h3>
                        <p class="mt-2 text-muted-foreground">
                            We may update our Privacy Policy from time to time. We will notify you of any changes by posting
                            the new Privacy Policy on this page and updating the "Last Updated" date. You are advised to review
                            this Privacy Policy periodically for any changes.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">11. Contact Us</h3>
                        <p class="mt-2 text-muted-foreground">
                            If you have any questions about this Privacy Policy, please contact us at:
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
                    By using our services, you acknowledge that you have read and understood this Privacy Policy.
                </p>
            </CardFooter>
        </Card>
    </AppLayout>
</template>
