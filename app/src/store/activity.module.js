import { activityService } from '../services';
import { router } from '../utils';

export const activity = {
    namespaced: true,
    state: { activities: [], activity: {}, week: 0, year: 0, thisMonday: "", thisSunday: "", totalHours: "", month: 0, monthTranslated:"", customers: []},
    actions: {
        getRecentActivities({ commit }) {
            activityService.getUserActivities()
            .then(
                activities => {
                commit('saveActivities',activities)
                }
            )
        },
        getActivity({ commit }, id) {
            activityService.getActivity(id)
            .then(
                activity => {
                commit('saveActivity',activity)
                }
            )
        },
        getWeekActivities({ commit }, date) {
            activityService.getUserActivitiesWeek(date)
            .then(
                response => {
                commit('saveWeekInfo',response);
                commit('saveActivities',response["activities"]);
                }
            )
        },
        getMonthActivities({ commit }, date) {
            activityService.getUserActivitiesMonth(date)
            .then(
                response => {
                commit('saveMonthInfo',response);
                commit('saveActivities',response["activities"]);
                }
            )
        },
        getYearActivities({ commit }, date) {
            activityService.getUserActivitiesYear(date)
            .then(
                response => {
                commit('saveYearInfo',response);
                commit('saveActivities',response["activities"]);
                }
            )
        },
        getCustomers({ commit }){
            activityService.getCustomers()
            .then(
                response => {
                commit('saveCustomers',response);
                }
            )
        },
        post({ commit },newActivity){
            activityService.postActivity(newActivity)
            .then(
                response => {
                    if(response){
                        router.push('/activities/week');
                    }
                }
            )
        }
    },
    mutations: {
        saveActivities(state, activities) {
            state.activities = activities;
        },
        saveActivity(state, activity) {
            state.activity = activity;
        },
        saveWeekInfo(state, response) {
            state.week = response["weekNr"];
            state.year = response["year"];
            state.thisMonday = response["thisMonday"];
            state.thisSunday = response["thisSunday"];
            state.totalHours = response["totalHours"];
        },
        saveMonthInfo(state, response) {
            state.month = response["month"];
            state.year = response["year"];
            state.monthTranslated = response["monthTranslated"];
            state.totalHours = response["totalHours"];
        },
        saveYearInfo(state, response) {
            state.year = response["year"];
            state.totalHours = response["totalHours"];
        },
        saveCustomers(state, response) {
            state.customers = response;
        }
    }
}
