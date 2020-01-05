<template>
    <div>

        <div class="page-header small">
            <div class="page-title">
                <h3>Login</h3>
            </div>
        </div>

      <div class="main">


        <div class=" card error-bar" v-if="errors.length">
            <ul>
                <li v-for="error in errors">{{ error }}</li>
            </ul>
        </div>

            <div class="card">

                <form action="" method="post" @submit.prevent="submit">
                    <div class="form-div">
                        <label for="username">Username</label>
                        <input type="text" v-model="username" name="username" class="form-control" />
                    </div>

                    <div class="form-div">
                        <label htmlFor="password">Password</label>
                        <input type="password" v-model="password" name="password" class="form-control" />
                    </div>
                   
                    <div class="form-div">
                        <input type="submit" value="Inloggen">
                    </div>
                </form>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    data () {
        return {
            errors: [],
            username: '',
            password: '',
        }
    },
    created () {
        this.$store.dispatch('authentication/logout');
    },
    methods: {
        submit () {
            //check form
            this.errors = [];
            const { username, password } = this;

            if(!username){
                this.errors.push("Vul aub een geldig emaildres in")
            }

            if(!password){
                this.errors.push("Vul aub een geldig wachtwoord in")
            }

            if(!this.errors.length){
                const { dispatch } = this.$store;
                dispatch('authentication/login', { username, password });
            }
            
            
        }
    }
}
</script>