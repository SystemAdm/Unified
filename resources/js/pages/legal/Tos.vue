<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { BookIcon } from 'lucide-vue-next';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: route('home') },
    { title: 'Terms of Service', href: route('legal.tos') },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Terms of Service" />

        <div class="mb-10 flex items-center">
            <BookIcon class="mr-3 h-8 w-8 text-muted-foreground" />
            <h2 class="text-3xl font-bold">Terms of Service</h2>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="text-2xl">Terms of Service</CardTitle>
                <CardDescription>Last Updated: {{ new Date().toLocaleDateString() }}</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold">1. Acceptance of Terms</h3>
                        <p class="mt-2 text-muted-foreground">
                            By accessing or using the Gaming Hub services, you agree to be bound by these Terms of Service.
                            If you do not agree to these terms, please do not use our services.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">2. Description of Service</h3>
                        <p class="mt-2 text-muted-foreground">
                            Gaming Hub provides a platform for gamers to connect, participate in events, access gaming resources,
                            and engage with the gaming community. Our services include event organization, game listings,
                            community forums, and membership benefits.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">3. User Accounts</h3>
                        <p class="mt-2 text-muted-foreground">
                            To access certain features of our service, you must register for an account. You are responsible for
                            maintaining the confidentiality of your account information and for all activities that occur under your account.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">4. User Conduct</h3>
                        <p class="mt-2 text-muted-foreground">
                            You agree not to use the service to:
                        </p>
                        <ul class="ml-6 mt-2 list-disc text-muted-foreground">
                            <li class="mt-1">Violate any laws or regulations</li>
                            <li class="mt-1">Infringe on the rights of others</li>
                            <li class="mt-1">Post or transmit harmful, threatening, abusive, harassing, defamatory, or otherwise objectionable content</li>
                            <li class="mt-1">Attempt to interfere with, compromise the system integrity or security, or decipher any transmissions to or from the servers</li>
                            <li class="mt-1">Take any action that imposes an unreasonable load on our infrastructure</li>
                        </ul>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">5. Membership</h3>
                        <p class="mt-2 text-muted-foreground">
                            Gaming Hub offers a membership option that provides additional benefits. Membership fees are non-refundable
                            except where required by law. We reserve the right to modify membership benefits and pricing with reasonable notice.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">6. Events and Activities</h3>
                        <p class="mt-2 text-muted-foreground">
                            Gaming Hub organizes and hosts various events. Participation in these events may require registration
                            and adherence to specific event rules. We reserve the right to refuse participation to anyone who violates
                            these terms or specific event guidelines.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">7. Content and Intellectual Property</h3>
                        <p class="mt-2 text-muted-foreground">
                            All content provided on Gaming Hub, including text, graphics, logos, images, and software, is the property
                            of Gaming Hub or its content suppliers and is protected by international copyright laws.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">8. Privacy</h3>
                        <p class="mt-2 text-muted-foreground">
                            Your privacy is important to us. Our <Link :href="route('legal.privacy')" target="_blank" class="underline hover:text-foreground">Privacy Policy</Link> explains how we collect, use, and protect your personal information.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">9. Limitation of Liability</h3>
                        <p class="mt-2 text-muted-foreground">
                            Gaming Hub shall not be liable for any indirect, incidental, special, consequential, or punitive damages
                            resulting from your access to or use of, or inability to access or use, the service.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">10. Termination</h3>
                        <p class="mt-2 text-muted-foreground">
                            We reserve the right to terminate or suspend your account and access to the service at our sole discretion,
                            without notice, for conduct that we believe violates these Terms of Service or is harmful to other users,
                            us, or third parties, or for any other reason.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">11. Changes to Terms</h3>
                        <p class="mt-2 text-muted-foreground">
                            We reserve the right to modify these Terms of Service at any time. We will provide notice of significant changes
                            through the service or by other means. Your continued use of the service after such modifications constitutes
                            your acceptance of the revised terms.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">12. Governing Law</h3>
                        <p class="mt-2 text-muted-foreground">
                            These Terms of Service shall be governed by and construed in accordance with the laws of Norway,
                            without regard to its conflict of law provisions.
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">13. Contact Information</h3>
                        <p class="mt-2 text-muted-foreground">
                            If you have any questions about these Terms of Service, please contact us at:
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            Email: info@gaminghub.com<br>
                            Phone: (555) 123-4567<br>
                            Address: 123 Gamer Street, Pixel City, PC 12345
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-xl font-semibold">14. User Roles and Responsibilities</h3>
                        <p class="mt-2 text-muted-foreground">
                            Gaming Hub has various user roles with different responsibilities and privileges. Understanding these roles
                            will help you navigate our community and know what to expect from different users.
                        </p>

                        <h4 class="mt-4 text-lg font-medium">14.1 Crew</h4>
                        <p class="mt-2 text-muted-foreground">
                            <strong>Role:</strong> Crew members are volunteers who help organize and run events, assist with community activities,
                            and provide support during gaming sessions.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>What to expect from them:</strong> Crew members provide assistance, answer questions, and ensure events
                            run smoothly. They are knowledgeable about Gaming Hub's services and can direct you to appropriate resources.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>What we expect of them:</strong> Crew members are expected to be helpful, courteous, and knowledgeable.
                            They should represent Gaming Hub positively and assist members and guests to the best of their ability.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>How to apply:</strong> Members can apply to become crew by submitting an application through their dashboard.
                            Applications are reviewed based on community involvement, reliability, and gaming knowledge.
                        </p>

                        <h4 class="mt-4 text-lg font-medium">14.2 Member</h4>
                        <p class="mt-2 text-muted-foreground">
                            <strong>Role:</strong> Members are registered users who have full access to Gaming Hub's services, including
                            event participation, forums, and community resources.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>What to expect from them:</strong> Members are expected to participate in the community respectfully
                            and follow community guidelines.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>What we expect of them:</strong> Members should adhere to our code of conduct, treat others with respect,
                            and contribute positively to the community.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>How to apply:</strong> Anyone can become a member by registering an account on our platform and agreeing
                            to our terms of service.
                        </p>

                        <h4 class="mt-4 text-lg font-medium">14.3 Guardian</h4>
                        <p class="mt-2 text-muted-foreground">
                            <strong>Role:</strong> Guardians are responsible for supervising and providing consent for users under 18 years old.
                            They ensure that younger users have a safe and appropriate experience.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>What to expect from them:</strong> Guardians monitor their dependent's activities, provide necessary
                            permissions, and ensure compliance with age-appropriate content guidelines.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>What we expect of them:</strong> Guardians must provide accurate information, actively supervise their
                            dependents, and promptly respond to communications regarding their dependent's account.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>How to apply:</strong> Adults can register as guardians during the account creation process for a minor,
                            providing verification of their relationship to the minor and accepting guardian responsibilities.
                        </p>

                        <h4 class="mt-4 text-lg font-medium">14.4 Guest</h4>
                        <p class="mt-2 text-muted-foreground">
                            <strong>Role:</strong> Guests are users who access limited features of Gaming Hub without creating an account.
                            They can view public information and attend certain events.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>What to expect from them:</strong> Guests have limited access to features and may be required to register
                            for full access to services.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>What we expect of them:</strong> Even without an account, guests must comply with our community guidelines
                            and terms of service when using our platform.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>How to apply:</strong> No application is needed for guest access. However, guests are encouraged to
                            register for a full member account to access all features.
                        </p>

                        <h4 class="mt-4 text-lg font-medium">14.5 Admin</h4>
                        <p class="mt-2 text-muted-foreground">
                            <strong>Role:</strong> Admins have administrative privileges and oversee the platform's operation. They manage
                            user accounts, content, and system settings.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>What to expect from them:</strong> Admins maintain the platform, enforce rules, and ensure the community
                            runs smoothly. They have the authority to make decisions regarding user accounts and content.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>What we expect of them:</strong> Admins must act with integrity, fairness, and transparency. They should
                            apply rules consistently and prioritize the community's well-being.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>How to apply:</strong> Admin positions are assigned internally based on experience, trustworthiness,
                            and technical knowledge. These are typically staff positions within the organization.
                        </p>

                        <h4 class="mt-4 text-lg font-medium">14.6 Moderator</h4>
                        <p class="mt-2 text-muted-foreground">
                            <strong>Role:</strong> Moderators monitor community interactions, enforce community guidelines, and help maintain
                            a positive environment.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>What to expect from them:</strong> Moderators review content, address rule violations, and facilitate
                            healthy discussions. They can issue warnings and temporary restrictions when necessary.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>What we expect of them:</strong> Moderators should be fair, consistent, and respectful when enforcing
                            rules. They must remain neutral in disputes and prioritize community standards.
                        </p>
                        <p class="mt-2 text-muted-foreground">
                            <strong>How to apply:</strong> Trusted members with a history of positive community involvement may be invited
                            to apply for moderator positions. Applications are reviewed based on communication skills, judgment, and community standing.
                        </p>
                    </div>
                </div>
            </CardContent>
            <CardFooter>
                <p class="text-sm text-muted-foreground">
                    By using our services, you acknowledge that you have read and understood these Terms of Service.
                </p>
            </CardFooter>
        </Card>
    </AppLayout>
</template>
