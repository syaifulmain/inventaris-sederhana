import http from 'k6/http';
import { check, group, sleep } from 'k6';

// Load Test Configuration
export const options = {
    stages: [
        { duration: '30s', target: 5 },
        { duration: '1m', target: 5 },
        { duration: '30s' , target: 0 },
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

// Base URL
const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000/api';

// Test Users
const TEST_USERS = [
    { email: 'admin@example.com', password: 'password' },
    { email: 'user1@example.com', password: 'password' },
    { email: 'user2@example.com', password: 'password' },
];

function getRandomUser() {
    return TEST_USERS[Math.floor(Math.random() * TEST_USERS.length)];
}


// Main test function
export default function () {
    const user = getRandomUser();
    let authToken = null;

    // Test 1: Login
    group('Auth - Login', function () {
        const loginRes = http.post(
            `${BASE_URL}/login`,
            JSON.stringify({
                email: user.email,
                password: user.password,
            }),
            {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            }
        );

        check(loginRes, {
            'login status is 200': (r) => r.status === 200,
            'login has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'login has token': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.data && body.data.token !== undefined;
                } catch (e) {
                    return false;
                }
            },
            'login response time < 1000ms': (r) => r.timings.duration < 1000,
        });

        // Extract token
        try {
            const body = JSON.parse(loginRes.body);
            authToken = body.data.token;
        } catch (e) {
            // Token extraction failed, skip other tests
            return;
        }
    });

    sleep(1);

    // Test 2: Get Profile
    if (authToken) {
        group('Auth - Get Profile', function () {
            const profileRes = http.get(
                `${BASE_URL}/profile`,
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${authToken}`,
                    },
                }
            );

            check(profileRes, {
                'profile status is 200': (r) => r.status === 200,
                'profile has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true;
                    } catch (e) {
                        return false;
                    }
                },
                'profile has user data': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.data && body.data.email === user.email;
                    } catch (e) {
                        return false;
                    }
                },
                'profile response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });

        sleep(1);
    }

    // Test 3: Update Profile
    if (authToken) {
        group('Auth - Update Profile', function () {
            const updateRes = http.put(
                `${BASE_URL}/profile`,
                JSON.stringify({
                    name: `Updated User ${Date.now()}`,
                }),
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${authToken}`,
                    },
                }
            );

            check(updateRes, {
                'update profile status is 200': (r) => r.status === 200,
                'update profile has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true;
                    } catch (e) {
                        return false;
                    }
                },
                'update profile has updated data': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.data && body.data.name !== undefined;
                    } catch (e) {
                        return false;
                    }
                },
                'update profile response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });

        sleep(1);
    }

    // Test 4: Logout
    if (authToken) {
        group('Auth - Logout', function () {
            const logoutRes = http.post(
                `${BASE_URL}/logout`,
                null,
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${authToken}`,
                    },
                }
            );

            check(logoutRes, {
                'logout status is 200': (r) => r.status === 200,
                'logout has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true;
                    } catch (e) {
                        return false;
                    }
                },
                'logout response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });
    }

    sleep(1);
}
