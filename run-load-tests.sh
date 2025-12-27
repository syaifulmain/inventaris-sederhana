#!/bin/bash

# K6 Load Testing Runner
# Description: Script untuk menjalankan semua K6 load tests
# Usage: ./run-load-tests.sh [options]

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
BASE_URL="${BASE_URL:-http://localhost:8000/api}"
TEST_DIR="tests/k6"
RESULTS_DIR="storage/load-test-results"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Create results directory
mkdir -p "$RESULTS_DIR"

# Banner
echo -e "${BLUE}"
echo "╔════════════════════════════════════════╗"
echo "║     K6 Load Testing Suite              ║"
echo "║     Inventaris Sederhana               ║"
echo "╚════════════════════════════════════════╝"
echo -e "${NC}"
echo "BASE_URL: $BASE_URL"
echo "Results will be saved to: $RESULTS_DIR"
echo ""

# Test files
TESTS=(
    "auth-api-test.js"
    "users-api-test.js"
    "categories-api-test.js"
    "products-api-test.js"
    "suppliers-api-test.js"
    "stock-transactions-api-test.js"
)

# Statistics
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Function to run a single test
run_test() {
    local test_file=$1
    local test_name="${test_file%.js}"

    TOTAL_TESTS=$((TOTAL_TESTS + 1))

    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}Running: ${test_name}${NC}"
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

    local result_file="${RESULTS_DIR}/${test_name}_${TIMESTAMP}.json"

    if BASE_URL="$BASE_URL" k6 run --out json="$result_file" "${TEST_DIR}/${test_file}"; then
        echo -e "${GREEN}✅ PASSED: ${test_name}${NC}"
        PASSED_TESTS=$((PASSED_TESTS + 1))
        return 0
    else
        echo -e "${RED}❌ FAILED: ${test_name}${NC}"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        return 1
    fi
}

# Main execution
main() {
    # Check if K6 is installed
    if ! command -v k6 &> /dev/null; then
        echo -e "${RED}Error: K6 is not installed${NC}"
        echo "Please install K6: https://k6.io/docs/getting-started/installation/"
        exit 1
    fi

    # Check if Laravel server is running
    if ! curl -s "${BASE_URL}/login" > /dev/null 2>&1; then
        echo -e "${RED}Warning: Laravel server might not be running at ${BASE_URL}${NC}"
        echo "Please start the server with: php artisan serve"
        read -p "Continue anyway? (y/n) " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            exit 1
        fi
    fi

    # Run all tests
    echo -e "${BLUE}Starting load tests...${NC}"
    echo ""

    for test in "${TESTS[@]}"; do
        run_test "$test"
        echo ""
        sleep 2  # Cooldown between tests
    done

    # Summary
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}Test Summary${NC}"
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "Total Tests:  ${TOTAL_TESTS}"
    echo -e "${GREEN}Passed:       ${PASSED_TESTS}${NC}"
    echo -e "${RED}Failed:       ${FAILED_TESTS}${NC}"

    SUCCESS_RATE=$(awk "BEGIN {printf \"%.2f\", ($PASSED_TESTS/$TOTAL_TESTS)*100}")
    echo -e "Success Rate: ${SUCCESS_RATE}%"
    echo ""

    if [ $FAILED_TESTS -eq 0 ]; then
        echo -e "${GREEN}🎉 All tests passed!${NC}"
        exit 0
    else
        echo -e "${RED}⚠️  Some tests failed. Check the results above.${NC}"
        exit 1
    fi
}

# Parse arguments
case "${1:-}" in
    --help|-h)
        echo "Usage: $0 [options]"
        echo ""
        echo "Options:"
        echo "  --help, -h     Show this help message"
        echo "  --auth         Run only auth tests"
        echo "  --categories   Run only categories tests"
        echo "  --products     Run only products tests"
        echo "  --suppliers    Run only suppliers tests"
        echo "  --stock        Run only stock transactions tests"
        echo ""
        echo "Environment Variables:"
        echo "  BASE_URL       API base URL (default: http://localhost:8000/api)"
        echo ""
        echo "Example:"
        echo "  BASE_URL=https://staging.example.com/api $0"
        exit 0
        ;;
    --auth)
        TESTS=("auth-api-test.js")
        ;;
    --categories)
        TESTS=("categories-api-test.js")
        ;;
    --products)
        TESTS=("products-api-test.js")
        ;;
    --suppliers)
        TESTS=("suppliers-api-test.js")
        ;;
    --stock)
        TESTS=("stock-transactions-api-test.js")
        ;;
esac

# Run main
main

