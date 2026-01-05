import {store} from '../store';
import {refreshToken} from '../store/slices/authSlice';
import {storageService} from './storageService';
import NetInfo from '@react-native-netinfo/netinfo';

// API Configuration
const API_BASE_URL = __DEV__ 
  ? 'http://localhost/ehealth' // Development URL
  : 'https://your-production-domain.com/ehealth'; // Production URL

interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  message?: string;
  error?: string;
}

interface RequestConfig {
  method?: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH';
  headers?: Record<string, string>;
  body?: any;
  requiresAuth?: boolean;
  timeout?: number;
}

class ApiService {
  private baseURL: string;
  private defaultTimeout: number = 30000; // 30 seconds

  constructor(baseURL: string) {
    this.baseURL = baseURL;
  }

  /**
   * Check network connectivity
   */
  private async checkConnectivity(): Promise<boolean> {
    const netInfo = await NetInfo.fetch();
    return netInfo.isConnected ?? false;
  }

  /**
   * Get authentication headers
   */
  private async getAuthHeaders(): Promise<Record<string, string>> {
    const token = await storageService.getSecureItem('accessToken');
    return token ? {Authorization: `Bearer ${token}`} : {};
  }

  /**
   * Handle token refresh
   */
  private async handleTokenRefresh(): Promise<boolean> {
    try {
      await store.dispatch(refreshToken()).unwrap();
      return true;
    } catch (error) {
      console.error('Token refresh failed:', error);
      return false;
    }
  }

  /**
   * Make HTTP request
   */
  private async makeRequest<T>(
    endpoint: string,
    config: RequestConfig = {},
  ): Promise<ApiResponse<T>> {
    const {
      method = 'GET',
      headers = {},
      body,
      requiresAuth = true,
      timeout = this.defaultTimeout,
    } = config;

    // Check connectivity
    const isConnected = await this.checkConnectivity();
    if (!isConnected) {
      throw new Error('No internet connection');
    }

    // Prepare headers
    let requestHeaders: Record<string, string> = {
      'Content-Type': 'application/json',
      ...headers,
    };

    // Add auth headers if required
    if (requiresAuth) {
      const authHeaders = await this.getAuthHeaders();
      requestHeaders = {...requestHeaders, ...authHeaders};
    }

    // Prepare request options
    const requestOptions: RequestInit = {
      method,
      headers: requestHeaders,
    };

    // Add body for non-GET requests
    if (body && method !== 'GET') {
      if (body instanceof FormData) {
        // Remove Content-Type for FormData (let browser set it)
        delete requestHeaders['Content-Type'];
        requestOptions.body = body;
      } else {
        requestOptions.body = JSON.stringify(body);
      }
    }

    // Create abort controller for timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), timeout);
    requestOptions.signal = controller.signal;

    try {
      const url = `${this.baseURL}/${endpoint}`;
      console.log(`API Request: ${method} ${url}`);
      
      const response = await fetch(url, requestOptions);
      clearTimeout(timeoutId);

      // Handle 401 Unauthorized - try token refresh
      if (response.status === 401 && requiresAuth) {
        const refreshSuccess = await this.handleTokenRefresh();
        if (refreshSuccess) {
          // Retry request with new token
          const newAuthHeaders = await this.getAuthHeaders();
          requestOptions.headers = {
            ...requestHeaders,
            ...newAuthHeaders,
          };
          const retryResponse = await fetch(url, requestOptions);
          return this.handleResponse<T>(retryResponse);
        }
      }

      return this.handleResponse<T>(response);
    } catch (error: any) {
      clearTimeout(timeoutId);
      
      if (error.name === 'AbortError') {
        throw new Error('Request timeout');
      }
      
      throw new Error(error.message || 'Network request failed');
    }
  }

  /**
   * Handle API response
   */
  private async handleResponse<T>(response: Response): Promise<ApiResponse<T>> {
    const contentType = response.headers.get('content-type');
    const isJson = contentType?.includes('application/json');

    let data: any;
    try {
      data = isJson ? await response.json() : await response.text();
    } catch (error) {
      data = null;
    }

    if (!response.ok) {
      const errorMessage = data?.message || data?.error || `HTTP ${response.status}`;
      throw new Error(errorMessage);
    }

    return {
      success: true,
      data,
      message: data?.message,
    };
  }

  /**
   * GET request
   */
  async get<T>(endpoint: string, config?: Omit<RequestConfig, 'method' | 'body'>): Promise<ApiResponse<T>> {
    return this.makeRequest<T>(endpoint, {...config, method: 'GET'});
  }

  /**
   * POST request
   */
  async post<T>(endpoint: string, body?: any, config?: Omit<RequestConfig, 'method'>): Promise<ApiResponse<T>> {
    return this.makeRequest<T>(endpoint, {...config, method: 'POST', body});
  }

  /**
   * PUT request
   */
  async put<T>(endpoint: string, body?: any, config?: Omit<RequestConfig, 'method'>): Promise<ApiResponse<T>> {
    return this.makeRequest<T>(endpoint, {...config, method: 'PUT', body});
  }

  /**
   * DELETE request
   */
  async delete<T>(endpoint: string, config?: Omit<RequestConfig, 'method' | 'body'>): Promise<ApiResponse<T>> {
    return this.makeRequest<T>(endpoint, {...config, method: 'DELETE'});
  }

  /**
   * PATCH request
   */
  async patch<T>(endpoint: string, body?: any, config?: Omit<RequestConfig, 'method'>): Promise<ApiResponse<T>> {
    return this.makeRequest<T>(endpoint, {...config, method: 'PATCH', body});
  }

  /**
   * Upload file
   */
  async uploadFile<T>(
    endpoint: string,
    file: {uri: string; type: string; name: string},
    additionalData?: Record<string, any>,
  ): Promise<ApiResponse<T>> {
    const formData = new FormData();
    
    // Add file
    formData.append('file', {
      uri: file.uri,
      type: file.type,
      name: file.name,
    } as any);

    // Add additional data
    if (additionalData) {
      Object.keys(additionalData).forEach(key => {
        formData.append(key, additionalData[key]);
      });
    }

    return this.makeRequest<T>(endpoint, {
      method: 'POST',
      body: formData,
    });
  }

  /**
   * Download file
   */
  async downloadFile(endpoint: string, filename: string): Promise<string> {
    const response = await this.makeRequest<Blob>(endpoint, {
      method: 'GET',
    });

    // Handle file download logic here
    // This would typically involve saving the blob to device storage
    // and returning the local file path
    
    return filename; // Placeholder
  }
}

// Create and export API service instance
export const apiService = new ApiService(API_BASE_URL);

// Export types
export type {ApiResponse, RequestConfig};