import AuthAPI from "../../api/auth";
import {
    AUTHENTICATING, AUTHENTICATING_SUCCESS,
    LOGIN_ERRORS, LOGOUT, REGISTER_ERRORS,
    TOKEN_REFRESH, TOKEN_REFRESH_ERROR, TOKEN_REFRESH_SUCCESS
} from "./constants";
import axios from "../../api/common";

export default {
    async login(state, payload) {
        state.commit(AUTHENTICATING);
        try {
            const response = await AuthAPI.login(payload);
            state.commit(AUTHENTICATING_SUCCESS, response.data);
            return Promise.resolve(response.data);
        } catch (error) {
            state.dispatch('logout', state);
            state.commit(LOGIN_ERRORS, error.response.data);
            return Promise.reject(error.response.data);
        }
    },

    async register(state, payload) {
        state.commit(AUTHENTICATING);
        try {
            const response = await AuthAPI.register(payload);
            state.commit(AUTHENTICATING_SUCCESS, response.data);
            return Promise.resolve(response.data);
        } catch (error) {
            state.dispatch('logout', state);
            state.commit(REGISTER_ERRORS, error.response.data);
            return Promise.reject(error.response.data);
        }
    },

    logout(state) {
        state.commit(LOGOUT);
        delete axios.defaults.headers.common['Authorization'];
        return Promise.resolve({'logout': true});
    },

    async refreshToken(state) {
        state.commit(TOKEN_REFRESH);
        try {
            const response = await AuthAPI.refreshToken(state.getters['refreshToken']);
            state.commit(TOKEN_REFRESH_SUCCESS, response.data);
            // Возвращаем 'response', так как в интерцепторе axios'а идет дальнейшая работа с ним
            return Promise.resolve(response);
        } catch (error) {
            state.dispatch('logout');
            state.commit(TOKEN_REFRESH_ERROR,  error.response.data.errors);
            return Promise.reject(error);
        }
    },
};