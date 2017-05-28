export var global = {
    icon: 'fa fa-credit-card',
    nav_id: 'payment',
    permission: 2,
    routes: [
        {
            title: 'Transactions',
            path: '/payment/dashboard',
            name: 'module:payment:dashboard',
            component: resolve => {
                require(['./components/PaymentDashboard.vue'], resolve)
            }
        }
    ]
};


export var routes = [
    {
        path: 'payment',
        name: 'module:payment',
        component: resolve => {
            require(['./components/Payment.vue'], resolve)
        }
    }
];

export var content_routes = {};

export default {
    global,
    routes,
    content_routes
}