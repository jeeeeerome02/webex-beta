export const siteName = 'Webex';

// ── Hero ──
export const heroTagline =
    'Zero hassle, just smarter exams. Try it now and see the difference!';

export const heroDescription =
    'Let AI help you create questions and review results automatically. Run exams smoothly without the usual hassle.';

export const heroCard2Title = 'AI-Powered Exams';

export const heroCard2Description =
    'Harness the power of artificial intelligence to generate high-quality exam questions, automate grading, ' +
    'and deliver real-time insights. Whether you\'re a teacher, professor, or institution, our platform is ' +
    'built to make assessment faster, fairer, and more insightful than ever before.';

// ── About ──
export const aboutTitle = 'Empowering Educators with Smart Exam Technology';

export const aboutDescription =
    'Our platform leverages cutting-edge artificial intelligence to transform the way exams are created, ' +
    'delivered, and graded. We believe that educators deserve better tools — tools that save time, ' +
    'reduce errors, and unlock deeper insights into student learning.';

export const aboutStats = [
    { value: '50K+', label: 'Exams Created' },
    { value: '12K+', label: 'Active Educators' },
    { value: '98%',  label: 'Grading Accuracy' },
    { value: '200+', label: 'Institutions' },
];

// ── Footer ──
export const footerTagline =
  'Smarter Online Examination starts here. Let AI help you create questions and review results automatically.';

export const productLinks = [
    { label: 'Features',            href: '#features' },
    { label: 'AI Question Generator', href: '#' },
    { label: 'Smart Grading',        href: '#' },
    { label: 'Student Analytics',    href: '#' },
    { label: 'Cloud Platform',        href: '#' },
];

export const companyLinks = [
    { label: 'About',  href: '#about' },
    { label: 'Blog',   href: '#' },
    { label: 'Careers', href: '#' },
    { label: 'Press',  href: '#' },
];

export const supportLinks = [
    { label: 'Help Center',   href: '#' },
    { label: 'Contact Us',    href: '#contact' },
    { label: 'Privacy Policy', href: '#' },
    { label: 'Terms of Service', href: '#' },
];

// ── Features / Footer Features (shared) ──
export const features = [
    {
        icon: '<i class="pi pi-bolt"></i>',
        title: 'AI-Powered Questions',
        description:
            'Automatically generate high-quality exam questions tailored to your curriculum with a single click.',
    },
    {
        icon: '<i class="pi pi-star"></i>',
        title: 'Smart Grading',
        description:
            'Let AI evaluate answers instantly and consistently, freeing up your time for what matters most.',
    },
    {
        icon: '<i class="pi pi-cog"></i>',
        title: 'Custom Templates',
        description:
            'Create and save exam templates that match your institution\'s format and difficulty standards.',
    },
    {
        icon: '<i class="pi pi-globe"></i>',
        title: 'Cloud-Based',
        description:
            'Access your exams and results anywhere, anytime — securely stored in the cloud.',
    },
    {
        icon: '<i class="pi pi-users"></i>',
        title: 'Student Analytics',
        description:
            'Detailed per-student insights and at-a-glance class performance dashboards.',
    },
    {
        icon: '<i class="pi pi-lock"></i>',
        title: 'Secure & Private',
        description:
            'End-to-end encryption and role-based access keep your data safe and compliant.',
    },
];

// ── Testimonials ──
export const testimonials = [
    {
        id: 1,
        name: 'Sarah Johnson',
        role: 'Product Manager',
        avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
        text: 'This platform has transformed how we conduct exams. The AI-powered question generation saves us hours of work each week.',
    },
    {
        id: 2,
        name: 'Michael Chen',
        role: 'University Professor',
        avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
        text: 'The automated grading feature is incredibly accurate and has reduced our workload significantly.',
    },
    {
        id: 3,
        name: 'Emily Rodriguez',
        role: 'High School Teacher',
        avatar: 'https://images.unsplash.com/photo-1503023345310-bd7c1de61c7d?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
        text: 'As a teacher, I love how easy it is to create customized assessments and track student progress.',
    },
];

// ── How It Works ──
export const howItWorks = [
    {
        step: 1,
        icon: '<i class="pi pi-file-edit"></i>',
        title: 'Create Your Exam',
        description:
            'Choose from AI-generated questions or build your own using our template library. Set difficulty, duration, and grading rules in minutes.',
    },
    {
        step: 2,
        icon: '<i class="pi pi-send"></i>',
        title: 'Distribute Instantly',
        description:
            'Share exams via email, links, or embed them in your LMS. Students can access them from any device — no installations required.',
    },
    {
        step: 3,
        icon: '<i class="pi pi-chart-bar"></i>',
        title: 'Auto-Grade & Analyze',
        description:
            'AI evaluates answers in real time. Get instant results, class-wide analytics, and detailed per-student performance reports.',
    },
];

// ── Pricing ──
export const pricingPlans = [
    {
        name: 'Starter',
        price: 'Free',
        period: 'forever',
        description: 'Perfect for individual educators getting started.',
        features: [
            'Up to 3 exams per month',
            'AI question generator (10 Qs/exam)',
            'Basic auto-grading',
            'Email support',
        ],
        cta: 'Get Started',
        highlight: false,
    },
    {
        name: 'Pro',
        price: '$29',
        period: '/month',
        description: 'For schools and departments with regular exam needs.',
        features: [
            'Unlimited exams',
            'AI question generator (unlimited)',
            'Advanced analytics & reports',
            'Custom templates',
            'Priority support',
        ],
        cta: 'Start Free Trial',
        highlight: true,
    },
    {
        name: 'Enterprise',
        price: '$99',
        period: '/month',
        description: 'For institutions needing full-scale deployment.',
        features: [
            'Everything in Pro',
            'LMS integration',
            'Role-based access control',
            'Dedicated account manager',
            'Custom branding',
            'SLA & compliance',
        ],
        cta: 'Contact Sales',
        highlight: false,
    },
];

// ── FAQ ──
export const faqItems = [
    {
        question: 'How does the AI question generator work?',
        answer: 'Our AI analyzes your curriculum materials and generates relevant questions across various formats — multiple choice, essay, fill-in-the-blank, and more. You can review, edit, and customize every question before including it in your exam.',
    },
    {
        question: 'Can I integrate with my existing LMS?',
        answer: 'Yes. Our Enterprise plan supports integration with popular Learning Management Systems including Canvas, Moodle, Blackboard, and Google Classroom. Setup takes just a few clicks.',
    },
    {
        question: 'Is my data secure?',
        answer: 'Absolutely. We use end-to-end encryption for all data in transit and at rest. Role-based access controls ensure only authorized users can view sensitive information. We are compliant with FERPA and GDPR.',
    },
    {
        question: 'What kind of support do you offer?',
        answer: 'Starter users get email support with a 48-hour response time. Pro users receive priority support with 12-hour responses. Enterprise customers get a dedicated account manager with 24/7 phone and chat support.',
    },
    {
        question: 'Can I try before I buy?',
        answer: 'Absolutely! The Pro plan comes with a 14-day free trial — no credit card required. You get full access to all Pro features, and you can cancel anytime during the trial period.',
    },
];
