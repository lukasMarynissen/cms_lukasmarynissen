import { userService } from '../services';
import { router } from '../utils';

const user = JSON.parse(localStorage.getItem('user'));
const initialState = user
    ? { status: { loggedIn: true }, user }
    : { status: {}, user: null };

export const authentication = {
    namespaced: true,
    state: initialState,
    actions: {
        login({ commit }, { username, password }) {
            commit('loginRequest', { username });
            userService.login(username, password)
                .then(
                    user => {
                        userService.getMe(user.token)
                        .then(user => {
                            commit('loginSuccess', user);
                            router.push('/');
                        })
                    },
                    error => {
                        commit('loginFailure', error);
                    }
                );
        },
        logout({ commit }) {
            userService.logout();
            commit('logout');
        }
    },
    mutations: {
        loginRequest(state, user) {
            state.user = user;
        },
        loginSuccess(state, user) {
            state.user = user;
        },
        loginFailure(state) {
            state.user = null;
        },
        logout(state) {
            state.user = null;
        }
    }
}
