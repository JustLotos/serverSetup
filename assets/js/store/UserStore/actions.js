import API from "../../api/user";
import {GETTING_USER_PROFILE, GETTING_USER_PROFILE_FAILURE, GETTING_USER_PROFILE_SUCCESS} from "./constants";

export default {
    async getUserProfile(context) {
        context.commit(GETTING_USER_PROFILE);
        try {
            const response = await API.getUserProfile();
            context.commit(GETTING_USER_PROFILE_SUCCESS, response.data);
            return Promise.resolve(response.data);
        } catch (error) {
            context.commit(GETTING_USER_PROFILE_FAILURE, error);
            return Promise.reject(error);
        }
    },
};