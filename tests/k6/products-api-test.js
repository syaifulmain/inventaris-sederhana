import http from 'k6/http';
import { check, group, sleep } from 'k6';

// Load Test Configuration
export const options = {
    stages: [
        { duration: '30s', target: 5 },
        { duration: '1m', target: 5 },
        { duration: '30s' , target: 0 },
        // { duration: '1m', target: 30 },
        // { duration: '2m', target: 50 },
        // { duration: '2m', target: 50 },
        // { duration: '1m', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<1500', 'p(99)<2000'],
        http_req_failed: ['rate<0.10'],
        checks: ['rate>0.80'],
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000/api';

const TEST_USERS = [
    { email: 'admin@example.com', password: 'password' },
    { email: 'user1@example.com', password: 'password' },
    { email: 'user2@example.com', password: 'password' },
];

function getRandomUser() {
    return TEST_USERS[Math.floor(Math.random() * TEST_USERS.length)];
}

function login() {
    const user = getRandomUser();
    const loginRes = http.post(
        `${BASE_URL}/login`,
        JSON.stringify({ email: user.email, password: user.password }),
        { headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' } }
    );

    if (loginRes.status === 200) {
        try {
            const body = JSON.parse(loginRes.body);
            return body.data.token;
        } catch (e) {
            return null;
        }
    }
    return null;
}

export default function () {
    const token = login();
    if (!token) return;

    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
    };

    let productId = null;
    let categoryId = 1; // Assuming category exists from seeder

    // Test 1: Get all products
    group('Products - Index', function () {
        const res = http.get(`${BASE_URL}/products`, { headers });

        check(res, {
            'Get all products status is 200': (r) => r.status === 200,
            'Get all products has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Get all products response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 2: Create product
    group('Products - Store', function () {
        const payload = JSON.stringify({
            code: `PROD-${Date.now()}`,
            category_id: categoryId,
            name: `Product Test ${Date.now()}`,
        });

        const res = http.post(`${BASE_URL}/products`, payload, { headers });

        // Debug response if error
        if (res.status !== 201) {
            console.log('Product creation failed:', res.status, res.body);
        }

        check(res, {
            'Create product status is 201': (r) => r.status === 201,
            'Create product has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    if (body.data && body.data.id) {
                        productId = body.data.id;
                    }
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Create product response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 3: Get single product
    if (productId) {
        group('Products - Show', function () {
            const res = http.get(`${BASE_URL}/products/${productId}`, { headers });

            check(res, {
                'Get single product status is 200': (r) => r.status === 200,
                'Get single product has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true && body.data.id == productId;
                    } catch (e) {
                        return false;
                    }
                },
                'Get single product response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });

        sleep(1);
    }

    // Test 4: Update product
    if (productId) {
        group('Products - Update', function () {
            const payload = JSON.stringify({
                code: `PROD-UPD-${Date.now()}`,
                category_id: categoryId,
                name: `Product Updated ${Date.now()}`,
            });

            const res = http.put(`${BASE_URL}/products/${productId}`, payload, { headers });

            check(res, {
                'Update product status is 200': (r) => r.status === 200,
                'Update product has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true;
                    } catch (e) {
                        return false;
                    }
                },
                'Update product response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });

        sleep(1);
    }

    // Test 5: Search products
    group('Products - Search', function () {
        const res = http.get(`${BASE_URL}/products?search=test`, { headers });

        check(res, {
            'Search products status is 200': (r) => r.status === 200,
            'Search products has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Search products response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 6: Filter products by category
    group('Products - Filter by Category', function () {
        const res = http.get(`${BASE_URL}/products?category_id=${categoryId}`, { headers });

        check(res, {
            'Filter products by category status is 200': (r) => r.status === 200,
            'Filter products by category has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Filter products by category response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 7: Pagination
    group('Products - Pagination', function () {
        const res = http.get(`${BASE_URL}/products?per_page=5`, { headers });

        check(res, {
            'status is 200': (r) => r.status === 200,
            'has pagination data': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.data && body.data.data !== undefined;
                } catch (e) {
                    return false;
                }
            },
            'response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 7: Delete product
    if (productId) {
        group('Products - Delete', function () {
            const res = http.del(`${BASE_URL}/products/${productId}`, null, { headers });

            check(res, {
                'status is 200': (r) => r.status === 200,
                'has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true;
                    } catch (e) {
                        return false;
                    }
                },
                'response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });
    }

    sleep(1);
}

