import http from 'k6/http';
import {check, group, sleep} from 'k6';

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
    {email: 'admin@example.com', password: 'password'},
    {email: 'user1@example.com', password: 'password'},
    {email: 'user2@example.com', password: 'password'},
];

function getRandomUser() {
    return TEST_USERS[Math.floor(Math.random() * TEST_USERS.length)];
}

function login() {
    const user = getRandomUser();
    const loginRes = http.post(
        `${BASE_URL}/login`,
        JSON.stringify({email: user.email, password: user.password}),
        {headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}}
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

    let categoryId = null;

    // Test 1: Get all categories
    group('Categories - Index', function () {
        const res = http.get(`${BASE_URL}/categories`, {headers});

        check(res, {
            'Get all categories status is 200': (r) => r.status === 200,
            'Get all categories has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Get all categories response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 2: Create category
    group('Categories - Store', function () {
        const payload = JSON.stringify({
            code: `Category Test ${Date.now()}`,
            name: 'Load test category',
        });

        const res = http.post(`${BASE_URL}/categories`, payload, {headers});

        check(res, {
            'Create category status is 201': (r) => r.status === 201,
            'Create category has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    if (body.data && body.data.id) {
                        categoryId = body.data.id;
                    }
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Create category response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 3: Get single category
    if (categoryId) {
        group('Categories - Show', function () {
            const res = http.get(`${BASE_URL}/categories/${categoryId}`, {headers});

            check(res, {
                'Get single category status is 200': (r) => r.status === 200,
                'Get single category has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true && body.data.id == categoryId;
                    } catch (e) {
                        return false;
                    }
                },
                'Get single category response time < 300ms': (r) => r.timings.duration < 300,
            });
        });

        sleep(1);
    }

    // Test 4: Update category
    if (categoryId) {
        group('Categories - Update', function () {
            const payload = JSON.stringify({
                name: `Category Updated ${Date.now()}`,
                description: 'Updated description',
            });

            const res = http.put(`${BASE_URL}/categories/${categoryId}`, payload, {headers});

            check(res, {
                'Update category status is 200': (r) => r.status === 200,
                'Update category has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true;
                    } catch (e) {
                        return false;
                    }
                },
                'Update category response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });

        sleep(1);
    }

    // Test 5: Search categories
    group('Categories - Search', function () {
        const res = http.get(`${BASE_URL}/categories?search=test`, {headers});

        check(res, {
            'Search categories status is 200': (r) => r.status === 200,
            'Search categories has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Search categories response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 6: Delete category
    if (categoryId) {
        group('Categories - Delete', function () {
            const res = http.del(`${BASE_URL}/categories/${categoryId}`, null, {headers});

            check(res, {
                'Delete category status is 200': (r) => r.status === 200,
                'Delete category has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true;
                    } catch (e) {
                        return false;
                    }
                },
                'Delete category response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });
    }

    sleep(1);
}

