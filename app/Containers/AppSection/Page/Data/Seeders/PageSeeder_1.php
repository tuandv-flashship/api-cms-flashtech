<?php

namespace App\Containers\AppSection\Page\Data\Seeders;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Page\Models\Page;
use App\Ship\Parents\Seeders\Seeder as ParentSeeder;

final class PageSeeder_1 extends ParentSeeder
{
    public function run(): void
    {
        $pages = [
            [
                'id' => 1,
                'name' => 'Homepage',
                'content' => '<div>[featured-posts enable_lazy_loading="yes"][/featured-posts]</div><div>[recent-posts title="What\'s new?" enable_lazy_loading="yes"][/recent-posts]</div><div>[featured-categories-posts title="Best for you" category_id="" enable_lazy_loading="yes"][/featured-categories-posts]</div><div>[all-galleries limit="6" title="Galleries" enable_lazy_loading="yes"][/all-galleries]</div>',
                'user_id' => 1,
                'image' => null,
                'template' => 'no-sidebar',
                'description' => null,
                'status' => ContentStatus::PUBLISHED,
            ],
            [
                'id' => 2,
                'name' => 'Blog',
                'content' => '---',
                'user_id' => 1,
                'image' => null,
                'template' => null,
                'description' => null,
                'status' => ContentStatus::PUBLISHED,
            ],
            [
                'id' => 3,
                'name' => 'Contact',
                'content' => '<h2>Get in Touch</h2><p>We\'d love to hear from you. Whether you have a question about features, trials, pricing, or anything else, our team is ready to answer all your questions.</p><h3>Our Office</h3><p>TechHub Innovation Center<br>123 Innovation Drive, Suite 400<br>San Francisco, CA 94105, USA</p><h3>Contact Information</h3><p>Phone: +1 (415) 555-0123</p><p>Email: hello@techhub.com</p><p>Support: support@techhub.com</p><h3>Business Hours</h3><p>Monday - Friday: 9:00 AM - 6:00 PM PST<br>Saturday - Sunday: Closed</p><p>[google-map]123 Innovation Drive, San Francisco, CA 94105, USA[/google-map]</p><h3>Send Us a Message</h3><p>Fill out the form below and we\'ll get back to you within 24 hours.</p><p>[contact-form][/contact-form]</p>',
                'user_id' => 1,
                'image' => null,
                'template' => null,
                'description' => null,
                'status' => ContentStatus::PUBLISHED,
            ],
            [
                'id' => 4,
                'name' => 'Cookie Policy',
                'content' => '<h3>EU Cookie Consent</h3><p>To use this website we are using Cookies and collecting some Data. To be compliant with the EU GDPR we give you to choose if you allow us to use certain Cookies and to collect some Data.</p><h4>Essential Data</h4><p>The Essential Data is needed to run the Site you are visiting technically. You can not deactivate them.</p><p>- Session Cookie: PHP uses a Cookie to identify user sessions. Without this Cookie the Website is not working.</p><p>- XSRF-Token Cookie: Laravel automatically generates a CSRF "token" for each active user session managed by the application. This token is used to verify that the authenticated user is the one actually making the requests to the application.</p>',
                'user_id' => 1,
                'image' => null,
                'template' => null,
                'description' => null,
                'status' => ContentStatus::PUBLISHED,
            ],
            [
                'id' => 5,
                'name' => 'Galleries',
                'content' => '<div>[gallery title="Galleries" enable_lazy_loading="yes"][/gallery]</div>',
                'user_id' => 1,
                'image' => null,
                'template' => null,
                'description' => null,
                'status' => ContentStatus::PUBLISHED,
            ],
            [
                'id' => 6,
                'name' => 'About Us',
                'content' => '<h2>About TechHub</h2><p>Founded in 2020, TechHub has quickly become a leading voice in technology journalism and innovation. Our mission is to demystify technology and make it accessible to everyone.</p><h3>Our Mission</h3><p>To provide insightful, accurate, and timely technology news and analysis that helps our readers understand and navigate the rapidly evolving digital landscape.</p><h3>What We Cover</h3><ul><li>Breaking tech news and announcements</li><li>In-depth product reviews and comparisons</li><li>Industry analysis and market trends</li><li>Startup spotlights and founder interviews</li><li>Technology tutorials and how-to guides</li></ul><h3>Our Team</h3><p>Our team consists of experienced technology journalists, industry analysts, and passionate tech enthusiasts who bring diverse perspectives and expertise to our coverage.</p><h3>Join Our Community</h3><p>With over 1 million monthly readers, TechHub has built a vibrant community of technology professionals, enthusiasts, and curious minds. Join us in exploring the future of technology.</p>',
                'user_id' => 1,
                'image' => null,
                'template' => null,
                'description' => null,
                'status' => ContentStatus::PUBLISHED,
            ],
            [
                'id' => 7,
                'name' => 'Privacy Policy',
                'content' => '<h2>Privacy Policy</h2><p><em>Last updated: November 29, 2025</em></p><h3>1. Information We Collect</h3><p>We collect information you provide directly to us, such as when you create an account, subscribe to our newsletter, or contact us for support.</p><h3>2. How We Use Your Information</h3><p>We use the information we collect to provide, maintain, and improve our services, send you technical notices and support messages, and respond to your comments and questions.</p><h3>3. Information Sharing</h3><p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as described in this policy.</p><h3>4. Data Security</h3><p>We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p><h3>5. Your Rights</h3><p>You have the right to access, update, or delete your personal information. You may also opt out of certain communications from us.</p><h3>6. Contact Us</h3><p>If you have any questions about this Privacy Policy, please contact us at privacy@techhub.com.</p>',
                'user_id' => 1,
                'image' => null,
                'template' => null,
                'description' => null,
                'status' => ContentStatus::PUBLISHED,
            ],
            [
                'id' => 8,
                'name' => 'Terms of Service',
                'content' => '<h2>Terms of Service</h2><p><em>Effective date: November 29, 2025</em></p><h3>1. Acceptance of Terms</h3><p>By accessing and using TechHub, you agree to be bound by these Terms of Service and all applicable laws and regulations.</p><h3>2. Use License</h3><p>Permission is granted to temporarily download one copy of the materials on TechHub for personal, non-commercial transitory viewing only.</p><h3>3. Disclaimer</h3><p>The materials on TechHub are provided on an \'as is\' basis. TechHub makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties.</p><h3>4. Limitations</h3><p>In no event shall TechHub or its suppliers be liable for any damages arising out of the use or inability to use the materials on TechHub.</p><h3>5. Revisions</h3><p>TechHub may revise these terms of service at any time without notice. By using this website, you are agreeing to be bound by the current version of these terms of service.</p>',
                'user_id' => 1,
                'image' => null,
                'template' => null,
                'description' => null,
                'status' => ContentStatus::PUBLISHED,
            ],
        ];

        foreach ($pages as $page) {
            Page::query()->firstOrCreate(
                ['id' => $page['id']],
                $page
            );
        }
    }
}
