// Simple script to test API authentication
const axios = require('axios');

// Configure axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

// First get the CSRF cookie
async function testAuthentication() {
  try {
    console.log('Fetching CSRF cookie...');
    await axios.get('http://fantasy.local:8000/sanctum/csrf-cookie');

    console.log('Testing API endpoint...');
    const response = await axios.get('http://fantasy.local:8000/api/leagues');
    console.log('Success! Response:', response.data);
  } catch (error) {
    console.error('Error:', error.response ? {
      status: error.response.status,
      statusText: error.response.statusText,
      data: error.response.data
    } : error.message);
  }
}

testAuthentication();
