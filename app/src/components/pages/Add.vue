<template>
    <div>
        <Header></Header>
        
        <div class="page-header small">
            <div class="page-title">
                <h3>Nieuwe activiteit toevoegen</h3>
            </div>
        </div>

        <div class="main">

            <div class="activities-options">
                <router-link class="small" :to="{ name: 'week'}">&lt; Terug naar activiteiten</router-link>
            </div>

            <div class=" card error-bar" v-if="errors.length">
                <ul>
                    <li v-for="error in errors">{{ error }}</li>
                </ul>
            </div>

            <div class="card">

                <form action="" method="post" @submit.prevent="submit">
                    <div class="form-div">
                        <label for="description">Datum</label>
                         <input type="date" v-model="date" >
                    </div>

                    <div class="form-row">
                        <div class="form-div">
                            <label for="start_time">Van</label>
                            <input type="time" id="start_time" v-model="start_time" step="300" >
                        </div>
                        <div class="form-div">
                            <label for="end_time">Tot</label>
                            <input type="time" id="end_time" v-model="end_time" step="300" >
                        </div>
                    </div>

                    <div class="form-div">
                        <label for="break_time">Pauze</label>
                        <div class="form-row">
                            <input type="number" name="" id="break_time" step="5" v-model="break_time">
                            <p>minuten</p>
                        </div>
                    </div>

                    <div class="form-div">
                        <label for="transport_distance">Transportafstand</label>
                        <div class="form-row">
                            <input type="number" name="" id="transport_distance" step="1" v-model="transport_distance">
                            <p>Kilometer</p>
                        </div>
                    </div>

                    <div class="form-div">
                        <label for="customer">Klant</label>
                        <select id="customer" v-model="customer">
                              <option v-for="customer in customers" :value="customer.id">{{ customer.companyName }}</option>
                        </select>
                    </div>
                    
                    <div class="form-div">
                        <label for="description">Beschrijving</label>
                        <input type="text" name="" id="description" v-model="description">
                    </div>

                    <div class="form-div">
                        <label for="used_materials">Gebruikte Materialen</label>
                        <input type="text" name="" id="used_materials" v-model="used_materials">
                    </div>

                    <div class="form-div">
                        <input type="submit" value="Activiteit Opslaan">
                    </div>
                </form>
            </div>

        </div>
        
    </div>



</template>

<script>

import Header from'../partials/Header';
import BottomBar from'../partials/BottomBar';
import Nav from'../partials/Nav';

import { userService, activityService } from '../../services';

export default {
    components: {
        Header,
        BottomBar,
        Nav
    },
    data () {
        return {
            errors: [],
            date: new Date().toISOString().substring(0, 10),
            start_time: '08:30',
            end_time: '16:00',
            description: '',
            used_materials: '',
            break_time: '0',
            transport_distance: '0',
            customer:''
        }
    },
    computed: {
        user () {
            return this.$store.state.authentication.user;
        },
        customers () {
            return this.$store.state.activity.customers;
        },
    },
    created () {
        this.$store.dispatch('activity/getCustomers');
    },
    watch: {
        '$route' (to, from) {
            this.$store.dispatch('activity/getMonthActivities', this.$route.params.date)
        }
    },
    methods: {
        submit () {

            //validation
            this.errors = [];
            const { date, start_time, end_time, description, used_materials, 
            break_time, transport_distance, customer } = this;

            if(!date){
                this.errors.push("Vul aub een geldige datum in")
            }

            if(!description){
                this.errors.push("Vul aub een geldige beschrijving in")
            }

            if(!customer){
                this.errors.push("Selecteer aub een klant")
            }

            if(!this.errors.length){
                this.$store.dispatch('activity/post', { date, start_time, end_time, description, used_materials, 
                break_time, transport_distance, customer });
            }
        }
    }

};
</script>