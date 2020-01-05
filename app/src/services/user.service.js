import { authHeader } from '../utils';

export const userService = {
    login,
    logout,
    getMe,
};

function login(username, password) {

    return fetch(`/api/auth/login`, 
    {   method: 'POST',
        headers: { 'Content-Type': 'application/json' }, 
        body: JSON.stringify({ username, password })
    })
    .then(handleResponse)
        .then(user => {
            if (user.token) {
                localStorage.setItem('user', JSON.stringify(user));
            }

            return user;
        });
}

function getMe(token){
    return fetch(`/api/me`, {
        method: 'GET',
        headers: authHeader()
    })
    .then(handleResponse)
    .then(user => {
        
        user.token = token;
        console.log(user);
        //this.$store.commit('user', user);
        localStorage.setItem('user', JSON.stringify(user));
        
        return user;
    });
}

function logout() {
    localStorage.removeItem('user');
}

function handleResponse(response) {
    console.log(response);
    return response.text().then(text => {
        const data = text && JSON.parse(text);
        if (!response.ok) {
            if (response.status === 401) {
                logout();
                location.reload(true);
            }

            const error = (data && data.message) || response.statusText;
            return Promise.reject(error);
        }

        return data;
    });
}