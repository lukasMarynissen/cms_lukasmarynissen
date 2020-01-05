import { authHeader } from '../utils';
import { userService } from '../services/user.service'

export const activityService = {
    getUserActivities,
    getUserActivitiesWeek,
    getUserActivitiesMonth,
    getUserActivitiesYear,
    getCustomers,
    postActivity,
    getActivity
};

function getUserActivities(){
    return fetch(`/api/activities/user`, {
        method: 'GET',
        headers: authHeader()
    })
    .then(handleResponse)
    .then(activities => {
        return activities;
    });
}

function getActivity(id){
    return fetch(`/api/activities/`+ id, {
        method: 'GET',
        headers: authHeader()
    })
    .then(handleResponse)
    .then(activity => {
        return activity;
    });
}

function getUserActivitiesWeek(date){
    return fetch(`/api/activities/user/week`, {
        method: 'POST',
        headers: authHeader(),
        body: JSON.stringify({ date })
    })
    .then(handleResponse)
    .then(response => {
        return response;
    });
}

function getUserActivitiesMonth(date){

    return fetch(`/api/activities/user/month`, {
        method: 'POST',
        headers: authHeader(),
        body: JSON.stringify({ date })
    })
    .then(handleResponse)
    .then(response => {
        return response;
    });
}

function getUserActivitiesYear(date){

    return fetch(`/api/activities/user/year`, {
        method: 'POST',
        headers: authHeader(),
        body: JSON.stringify({ date })
    })
    .then(handleResponse)
    .then(response => {
        return response;
    });
}

function getCustomers(){

    return fetch(`/api/customers`, {
        method: 'GET',
        headers: authHeader()
    })
    .then(handleResponse)
    .then(response => {
        return response;
    });
}

function postActivity(newActivity){
    return fetch(`/api/activities/new`, {
        method: 'POST',
        headers: authHeader(),
        body: JSON.stringify(newActivity)
    })
    .then(handleResponse)
    .then(response => {
        return response;
    });
}


function handleResponse(response) {
    return response.text().then(text => {
        console.log(text);
        const data = text && JSON.parse(text);
        if (!response.ok) {
            if (response.status === 401) {
                userService.logout();
                location.reload(true);
            }

            const error = (data && data.message) || response.statusText;
            return Promise.reject(error);
        }

        return data;
    });
}