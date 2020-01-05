import Vue from 'vue';
import Router from 'vue-router';

import Week from '../components/pages/Week'
import Month from '../components/pages/Month'
import Year from '../components/pages/Year'
import Login from '../components/pages/Login'
import Add from '../components/pages/Add'
import Detail from '../components/pages/Detail'

Vue.use(Router);

export const router = new Router({
  mode: 'history',
  routes: [
    { path: '/', name: 'home', redirect: '/activities/week'},
    { path: '/login', component: Login },
    { path: '/activities/week/:date?', component: Week, name: 'week' },
    { path: '/activities/month/:date?', component: Month, name: 'month' },
    { path: '/activities/year/:date?', component: Year, name: 'year' },
    { path: '/activities/add', component: Add, name: 'add' },
    { path: '/activities/:id', component: Detail, name: 'detail' },
    { path: '*', redirect: '/' }
  ]
});

router.beforeEach((to, from, next) => {

  const publicPages = ['/login'];
  const authRequired = !publicPages.includes(to.path);
  const loggedIn = localStorage.getItem('user');

  if (authRequired && !loggedIn) {
    return next('/login');
  }

  next();
})