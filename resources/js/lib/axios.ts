import axios from 'axios';

// Function to get CSRF cookie from the server
export const getCsrfToken = async () => {
  await axios.get('/sanctum/csrf-cookie');
};

// Configure axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

// Add a request interceptor to include the CSRF token
axios.interceptors.request.use(function (config) {
  // Get the CSRF token from the meta tag
  const token = document.head.querySelector('meta[name="csrf-token"]');

  if (token) {
    config.headers['X-CSRF-TOKEN'] = (token as HTMLMetaElement).content;
  }

  return config;
}, function (error) {
  return Promise.reject(error);
});

// Add response interceptor to handle common errors
axios.interceptors.response.use(
  response => response,
  error => {
    // Only redirect on 401 for non-API routes
    if (error.response && error.response.status === 401) {
      // Don't redirect for API routes, let the component handle it
      if (!error.config.url.startsWith('/api/')) {
        window.location.href = '/login';
      }
    }

    if (error.response && error.response.status === 419) {
      // CSRF token mismatch, reload the page to get a fresh token
      window.location.reload();
    }

    return Promise.reject(error);
  }
);

export default axios;
