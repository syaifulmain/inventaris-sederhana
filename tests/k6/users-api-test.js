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

// Only admin can access user management endpoints
const ADMIN_USER = { email: 'admin@example.com', password: 'password' };

function loginAsAdmin() {
    const loginRes = http.post(
        `${BASE_URL}/login`,
        JSON.stringify({ email: ADMIN_USER.email, password: ADMIN_USER.password }),
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
    const token = loginAsAdmin();
    if (!token) {
        console.log('Admin login failed');
        return;
    }

    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
    };

    let userId = null;

    // Test 1: Get all users
    group('Users - Index', function () {
        const res = http.get(`${BASE_URL}/admin/users`, { headers });

        check(res, {
            'Get all users status is 200': (r) => r.status === 200,
            'Get all users has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Get all users response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 2: Create user
    group('Users - Store', function () {
        const payload = JSON.stringify({
            name: `Test User ${Date.now()}`,
            email: `testuser${Date.now()}@example.com`,
            password: 'password123',
            password_confirmation: 'password123',
            role: 'user',
            is_active: true,
        });

        const res = http.post(`${BASE_URL}/admin/users`, payload, { headers });

        // Debug response if error
        if (res.status !== 201) {
            console.log('User creation failed:', res.status, res.body);
        }

        check(res, {
            'Create user status is 201': (r) => r.status === 201,
            'Create user has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    if (body.data && body.data.id) {
                        userId = body.data.id;
                    }
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Create user response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 3: Get single user
    if (userId) {
        group('Users - Show', function () {
            const res = http.get(`${BASE_URL}/admin/users/${userId}`, { headers });

            check(res, {
                'Get single user status is 200': (r) => r.status === 200,
                'Get single user has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true && body.data.id == userId;
                    } catch (e) {
                        return false;
                    }
                },
                'Get single user response time < 300ms': (r) => r.timings.duration < 300,
            });
        });

        sleep(1);
    }

    // Test 4: Update user
    if (userId) {
        group('Users - Update', function () {
            const payload = JSON.stringify({
                name: `Updated User ${Date.now()}`,
                email: `updated${Date.now()}@example.com`,
                role: 'user',
                is_active: true,
            });

            const res = http.put(`${BASE_URL}/admin/users/${userId}`, payload, { headers });

            check(res, {
                'Update user status is 200': (r) => r.status === 200,
                'Update user has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true;
                    } catch (e) {
                        return false;
                    }
                },
                'Update user response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });

        sleep(1);
    }

    // Test 5: Search users
    group('Users - Search', function () {
        const res = http.get(`${BASE_URL}/admin/users?search=test`, { headers });

        check(res, {
            'Search users status is 200': (r) => r.status === 200,
            'Search users has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Search users response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 6: Filter users by role
    group('Users - Filter by Role', function () {
        const res = http.get(`${BASE_URL}/admin/users?role=user`, { headers });

        check(res, {
            'Filter by role status is 200': (r) => r.status === 200,
            'Filter by role has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Filter by role response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 7: Pagination
    group('Users - Pagination', function () {
        const res = http.get(`${BASE_URL}/admin/users?per_page=5`, { headers });

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

    // Test 8: Delete user
    if (userId) {
        group('Users - Delete', function () {
            const res = http.del(`${BASE_URL}/admin/users/${userId}`, null, { headers });

            check(res, {
                'Delete user status is 200': (r) => r.status === 200,
                'Delete user has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true;
                    } catch (e) {
                        return false;
                    }
                },
                'Delete user response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });
    }

    sleep(1);
}

