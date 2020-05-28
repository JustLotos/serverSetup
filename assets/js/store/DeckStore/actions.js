import {
    CREATING_DECK, CREATING_DECK_ERROR, CREATING_DECK_SUCCESS,
    DELETING_DECK, DELETING_DECK_ERROR, DELETING_DECK_SUCCESS,
    FETCHING_DECKS, FETCHING_DECKS_ERROR, FETCHING_DECKS_SUCCESS,
    GETTING_DECK, GETTING_DECK_ERROR, GETTING_DECK_SUCCESS,
    UPDATING_DECK, UPDATING_DECK_ERROR, UPDATING_DECK_SUCCESS,
} from "./constants";
import {FETCHING_CARDS_SUCCESS} from "../CardStore/constants";
import API from "../../api/deck";

export default {
    async getAll(context) {
        context.commit(FETCHING_DECKS);
        try {
            const response = await API.getAll();
            context.commit(FETCHING_DECKS_SUCCESS, response.data);
            response.data.forEach((deck)=>{
                context.commit('CardStore/'+FETCHING_CARDS_SUCCESS, deck, {root: true});
            })
            return Promise.resolve(response.data);
        } catch (errors) {
            context.commit(FETCHING_DECKS_ERROR, errors.response.data.errors);
            return Promise.reject(errors.response.data.errors);
        }
    },

    async getOne(context, deck) {
        context.commit(GETTING_DECK);
        try {
            const response = await API.getOne(deck.id);
            context.commit(GETTING_DECK_SUCCESS, response.data);
            context.commit('CardStore/'+FETCHING_CARDS_SUCCESS, response.data, { root: true });
            return Promise.resolve(response.data);
        } catch (errors) {
            context.commit(GETTING_DECK_ERROR, errors.response.data.errors);
            return Promise.reject(errors.response.data.errors);
        }
    },

    async create(context, deck) {
        context.commit(CREATING_DECK);
        try {
            let response = await API.create(deck);
            context.commit(CREATING_DECK_SUCCESS, response.data);
            return Promise.resolve(response.data);
        } catch (errors) {
            context.commit(CREATING_DECK_ERROR, errors.response.data.errors);
            return Promise.reject(errors.response.data.errors);
        }
    },

    async update(context, deck) {
        context.commit(UPDATING_DECK);
        try {
            let response = await API.update(deck);
            context.commit(UPDATING_DECK_SUCCESS, response.data);
            return Promise.resolve(response.data);
        } catch (errors) {
            context.commit(UPDATING_DECK_ERROR, errors.response.data.errors);
            return Promise.reject(errors.response.data.errors);
        }
    },

    async delete(context, deck) {
        context.commit(DELETING_DECK);
        try {
            let response = await API.delete(deck.id);
            context.commit(DELETING_DECK_SUCCESS, deck);
            return Promise.resolve(response.data);
        } catch (errors) {
            context.commit(DELETING_DECK_ERROR, errors.response.data.errors);
            return Promise.reject(errors.response.data.errors);
        }
    }
};