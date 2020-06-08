import axios, {AxiosRequestConfig} from "axios";
import {AuthModule} from "../Domain/Auth/AuthModule";
import Router from "../Router";
import {AuthResponse} from "../Domain/Auth/types";

let API_URL = process.env.APP_HOST + '/api/v1';

let Axios = axios.create({
    baseURL: process.env.API_URL || 'http://flash.local/api/v1',
    headers: {
        "Content-type": "application/json"
    }
});

Axios.interceptors.response.use(
    (response) => { return response },
    function (error) {
        const originalRequest: AxiosRequestConfig = error.config;

        AuthModule.loading(false);
        if (error.response?.status === 401 && originalRequest.url === originalRequest.baseURL + '/auth/token/refresh') {
            AuthModule.logout();
            Router.push({name: 'Login'});
        }

        if (
            error.response?.status === 401 && !originalRequest._retry &&
            originalRequest.url !== '/auth/login' &&
            originalRequest.url !== '/auth/register'&&
            originalRequest.url !== '/auth/reset/password'
        ) {
            originalRequest._retry = true;
            return AuthModule.refresh()
                .then((response: AuthResponse) => {
                    return Axios(originalRequest.headers.common['Authorization'] = 'Bearer' + AuthModule.token);
                })
                .catch(()=>{
                    AuthModule.logout();
                    Router.push({name: 'Login'});
                });
        }
        return Promise.reject(error);
    }
);

Axios.interceptors.request.use(
    config => {
        if (AuthModule.isAuthenticated) {
            config.headers['Authorization'] = 'Bearer ' + AuthModule.token;
        }
        return config;
    },
    error => Promise.reject(error)
);

export default Axios;