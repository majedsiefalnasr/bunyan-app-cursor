export default defineI18nConfig(() => ({
    legacy: false,
    locale: 'ar',
    messages: {
        ar: {
            app: {
                name: 'بنيان',
                description: 'منصة الخدمات الإنشائية والمواد البناء العربية',
            },
            nav: {
                home: 'الرئيسية',
                projects: 'المشاريع',
                services: 'الخدمات',
                products: 'المنتجات',
                contact: 'اتصل بنا',
            },
            auth: {
                login: 'تسجيل الدخول',
                register: 'إنشاء حساب',
                logout: 'تسجيل الخروج',
            },
        },
        en: {
            app: {
                name: 'Bunyan',
                description: 'Arabic Construction Services and Building Materials Marketplace',
            },
            nav: {
                home: 'Home',
                projects: 'Projects',
                services: 'Services',
                products: 'Products',
                contact: 'Contact',
            },
            auth: {
                login: 'Login',
                register: 'Sign Up',
                logout: 'Logout',
            },
        },
    },
}));
