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

function createCategory(headers) {
    const payload = JSON.stringify({
        code: `CAT-${Date.now()}`,
        name: `Category for Stock Test ${Date.now()}`,
    });
    const res = http.post(`${BASE_URL}/categories`, payload, { headers });
    if (res.status === 201) {
        try {
            const body = JSON.parse(res.body);
            return body.data.id;
        } catch (e) {
            return null;
        }
    }
    return null;
}

function createProduct(headers, categoryId) {
    const payload = JSON.stringify({
        code: `PROD-${Date.now()}`,
        category_id: categoryId,
        name: `Product for Stock Test ${Date.now()}`,
    });
    const res = http.post(`${BASE_URL}/products`, payload, { headers });
    if (res.status === 201) {
        try {
            const body = JSON.parse(res.body);
            return body.data.id;
        } catch (e) {
            return null;
        }
    }
    return null;
}

function createSupplier(headers) {
    const payload = JSON.stringify({
        code: `SUP-${Date.now()}`,
        name: `Supplier for Stock Test ${Date.now()}`,
        address: 'Test Address',
    });
    const res = http.post(`${BASE_URL}/suppliers`, payload, { headers });
    if (res.status === 201) {
        try {
            const body = JSON.parse(res.body);
            return body.data.id;
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

    // Create test data
    const categoryId = createCategory(headers);
    if (!categoryId) {
        console.log('Failed to create category');
        return;
    }

    const productId = createProduct(headers, categoryId);
    if (!productId) {
        console.log('Failed to create product');
        return;
    }

    const supplierId = createSupplier(headers);
    if (!supplierId) {
        console.log('Failed to create supplier');
        return;
    }

    let transactionId = null;

    // Test 1: Get all stock transactions
    group('Stock Transactions - Index', function () {
        const res = http.get(`${BASE_URL}/stock-transactions`, { headers });

        check(res, {
            'Get all stock transactions status is 200': (r) => r.status === 200,
            'Get all stock transactions has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Get all stock transactions response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 2: Create stock transaction (IN)
    group('Stock Transactions - Store (IN)', function () {
        const payload = JSON.stringify({
            product_id: productId,
            supplier_id: supplierId,
            type: 'in',
            quantity: 10,
            transaction_date: new Date().toISOString().split('T')[0],
            notes: `Load test IN ${Date.now()}`,
        });

        const res = http.post(`${BASE_URL}/stock-transactions`, payload, { headers });

        // Debug response if error
        if (res.status !== 201) {
            console.log('Stock transaction IN creation failed:', res.status, res.body);
        }

        check(res, {
            'Create stock transaction (IN) status is 201': (r) => r.status === 201,
            'Create stock transaction (IN) has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    if (body.data && body.data.id) {
                        transactionId = body.data.id;
                    }
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Create stock transaction (IN) response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 3: Create stock transaction (OUT)
    group('Stock Transactions - Store (OUT)', function () {
        const payload = JSON.stringify({
            product_id: productId,
            supplier_id: supplierId,
            type: 'out',
            quantity: 5,
            transaction_date: new Date().toISOString().split('T')[0],
            notes: `Load test OUT ${Date.now()}`,
        });

        const res = http.post(`${BASE_URL}/stock-transactions`, payload, { headers });

        // Debug response if error
        if (res.status !== 201) {
            console.log('Stock transaction OUT creation failed:', res.status, res.body);
        }

        check(res, {
            'Create stock transaction (OUT) status is 201': (r) => r.status === 201,
            'Create stock transaction (OUT) has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Create stock transaction (OUT) response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 4: Get single transaction
    if (transactionId) {
        group('Stock Transactions - Show', function () {
            const res = http.get(`${BASE_URL}/stock-transactions/${transactionId}`, { headers });

            check(res, {
                'Get single transaction status is 200': (r) => r.status === 200,
                'Get single transaction has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true && body.data.id == transactionId;
                    } catch (e) {
                        return false;
                    }
                },
                'Get single transaction response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });

        sleep(1);
    }

    // Test 5: Update transaction
    if (transactionId) {
        group('Stock Transactions - Update', function () {
            const payload = JSON.stringify({
                product_id: productId,
                type: 'in',
                quantity: 15,
                transaction_date: new Date().toISOString().split('T')[0],
                notes: `Updated load test ${Date.now()}`,
            });

            const res = http.put(`${BASE_URL}/stock-transactions/${transactionId}`, payload, { headers });

            check(res, {
                'Update transaction status is 200': (r) => r.status === 200,
                'Update transaction has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true;
                    } catch (e) {
                        return false;
                    }
                },
                'Update transaction response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });

        sleep(1);
    }

    // Test 6: Filter by product
    group('Stock Transactions - Filter by Product', function () {
        const res = http.get(`${BASE_URL}/stock-transactions?product_id=${productId}`, { headers });

        if (res.status !== 200) {
            console.log('Filter by product failed:', res.status, res.body);
        }

        check(res, {
            'Filter by product status is 200': (r) => r.status === 200,
            'Filter by product has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Filter by product response time < 500ms': (r) => r.timings.duration < 500,
        });
    });

    sleep(1);

    // Test 7: Filter by supplier
    group('Stock Transactions - Filter by Supplier', function () {
        const res = http.get(`${BASE_URL}/stock-transactions?supplier_id=${supplierId}`, { headers });

        check(res, {
            'Filter by supplier status is 200': (r) => r.status === 200,
            'Filter by supplier has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Filter by supplier response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 8: Filter by date range
    group('Stock Transactions - Filter by Type', function () {
        const res = http.get(`${BASE_URL}/stock-transactions?type=in`, { headers });

        check(res, {
            'Filter by date range status is 200': (r) => r.status === 200,
            'Filter by date range has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Filter by date range response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 9: Search transactions
    group('Stock Transactions - Search', function () {
        const res = http.get(`${BASE_URL}/stock-transactions?search=test`, { headers });

        check(res, {
            'Search transactions status is 200': (r) => r.status === 200,
            'Search transactions has success response': (r) => {
                try {
                    const body = JSON.parse(r.body);
                    return body.success === true;
                } catch (e) {
                    return false;
                }
            },
            'Search transactions response time < 1000ms': (r) => r.timings.duration < 1000,
        });
    });

    sleep(1);

    // Test 10: Delete transaction
    if (transactionId) {
        group('Stock Transactions - Delete', function () {
            const res = http.del(`${BASE_URL}/stock-transactions/${transactionId}`, null, { headers });

            check(res, {
                'Delete transaction status is 200': (r) => r.status === 200,
                'Delete transaction has success response': (r) => {
                    try {
                        const body = JSON.parse(r.body);
                        return body.success === true;
                    } catch (e) {
                        return false;
                    }
                },
                'Delete transaction response time < 1000ms': (r) => r.timings.duration < 1000,
            });
        });
    }

    sleep(1);
}

