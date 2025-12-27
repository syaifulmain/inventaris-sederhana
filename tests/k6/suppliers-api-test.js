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

    let supplierId = null;

    // Test 1: Get all suppliers
    group('Suppliers - Index', function () {
        const res = http.get(`${BASE_URL}/suppliers`, { headers });

        check(res, {
            'Get all suppliers status is 200': (r) => r.status === 200,
            'Get all suppliers has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Get all suppliers response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 2: Create supplier
    group('Suppliers - Store', function () {
        const payload = JSON.stringify({
            code: `SUP-${Date.now()}`,
            name: `Supplier Test ${Date.now()}`,
            address: 'Test Address',
        });

        const res = http.post(`${BASE_URL}/suppliers`, payload, { headers });

        // Debug response if error
        if (res.status !== 201) {
            console.log('Supplier creation failed:', res.status, res.body);
        }

        check(res, {
            'Create supplier status is 201': (r) => r.status === 201,
            'Create supplier has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    if (body.data && body.data.id) {
                        supplierId = body.data.id;
                    }
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Create supplier response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 3: Get single supplier
    if (supplierId) {
        group('Suppliers - Show', function () {
            const res = http.get(`${BASE_URL}/suppliers/${supplierId}`, { headers });

            check(res, {
                'Get single supplier status is 200': (r) => r.status === 200,
                'Get single supplier has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true && body.data.id == supplierId;
                    } catch (e) {
                        return false;
                    }
                },
                'Get single supplier response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });

        sleep(1);
    }

    // Test 4: Update supplier
    if (supplierId) {
        group('Suppliers - Update', function () {
            const payload = JSON.stringify({
                code: `${Date.now()}`,
                name: `Supplier Updated ${Date.now()}`,
                address: 'Updated Address',
            });

            const res = http.put(`${BASE_URL}/suppliers/${supplierId}`, payload, { headers });

            check(res, {
                'Update supplier status is 200': (r) => r.status === 200,
                'Update supplier has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true;
                    } catch (e) {
                        return false;
                    }
                },
                'Update supplier response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });

        sleep(1);
    }

    // Test 5: Search suppliers
    group('Suppliers - Search', function () {
        const res = http.get(`${BASE_URL}/suppliers?search=test`, { headers });

        check(res, {
            'Search suppliers status is 200': (r) => r.status === 200,
            'Search suppliers has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Search suppliers response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 6: Pagination
    group('Suppliers - Pagination', function () {
        const res = http.get(`${BASE_URL}/suppliers?per_page=5`, { headers });

        check(res, {
            'Pagination status is 200': (r) => r.status === 200,
            'Pagination has pagination data': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.data && body.data.data !== undefined;
                } catch (e) {
                    return false;
                }
            },
            'Pagination response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 6: Filter suppliers
    if (supplierId) {
        group('Suppliers - Delete', function () {
            const res = http.del(`${BASE_URL}/suppliers/${supplierId}`, null, { headers });

            check(res, {
                'Filter suppliers status is 200': (r) => r.status === 200,
                'Filter suppliers has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true;
                    } catch (e) {
                        return false;
                    }
                },
                'Filter suppliers response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });
    }

    sleep(1);
}

