<template>
    <div class="container">
        <div class="row mt-5" v-if="$gate.isAdminOrAuthor()">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Users Table</h3>

                <div class="card-tools">
                    <button class='btn btn-success' @click="newModal">Add New User <i class="fas fa-user-plus fa-fw"></i></button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Type</th>
                      <th>Registered At</th>
                      <th>Modify</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="user in users.data" :key="user.id">
                      <td>{{user.id}}</td>
                      <td>{{user.name | upperText}}</td>
                      <td>{{user.email}}</td>                      
                      <td>{{user.type}}</td>
                      <td>{{user.created_at | dateAgo}}</td>
                      <td>
                          <a href="#" @click="editModal(user)">
                              <i class="fa fa-edit blue"></i>                          
                          </a>
                          /
                          <a href="#" @click="deleteUser(user.id)">
                               <i class="fa fa-trash red"></i> 
                          </a>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                  <pagination :data="users" @pagination-change-page="getResults"></pagination>
              </div>
            </div>
            <!-- /.card -->
          </div>
        </div>

        <div v-if="!$gate.isAdminOrAuthor()">
            <not-found></not-found>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="AddNew" tabindex="-1" role="dialog" aria-labelledby="AddNewLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 v-show="!editmode" class="modal-title" id="AddNewLabel">Add New</h5>
                    <h5 v-show="editmode" class="modal-title" id="AddNewLabel">Update Info</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form @submit.prevent="editmode ? editUser() : createUser()">
                    <div class="modal-body">
                        <div class="form-group">
                            <input v-model="form.name" type="text" name="name" placeholder="Enter your name"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                            <has-error :form="form" field="name"></has-error>
                        </div>
                        <div class="form-group">
                            <input v-model="form.email" type="email" name="email" placeholder="Enter your Email"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                            <has-error :form="form" field="email"></has-error>
                        </div>
                        <div class="form-group">
                            <input v-model="form.password" type="password" name="password" placeholder="Enter your password"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
                            <has-error :form="form" field="password"></has-error>
                        </div>
                        <div class="form-group">
                            <textarea v-model="form.bio" name="bio" placeholder="Short bio for User"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('bio') }"></textarea>
                            <has-error :form="form" field="bio"></has-error>
                        </div>
                        <div class="form-group">
                            <select v-model="form.type" name="type"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('type') }">
                                <option value="">Select role for user</option>
                                <option value="admin">Admin</option>
                                <option value="user">Standar User</option>
                                <option value="author">Author</option>
                            </select>
                            <has-error :form="form" field="type"></has-error>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button v-show="editmode" type="submit" class="btn btn-success">Update</button>
                        <button v-show="!editmode" type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
                </div>
            </div>
        </div>
        <!-- End Modal -->
    </div>    
</template>

<script>
import { setInterval } from 'timers';
    export default {
        
        data(){
            return{
                editmode : false,
                users : {},
                form : new Form({
                    id:'',
                    name:'',
                    email:'',
                    password:'',
                    type:'',
                    bio:'',
                    photo:''
                })
            }
        },
        methods :{
            
            getResults(page = 1) {
                axios.get('api/user?page=' + page)
                    .then(response => {
                        this.users = response.data;
                    });
            },
            
            editModal(data){
                this.editmode = true;
                this.form.reset();
                $('#AddNew').modal('show');
                this.form.fill(data);
            },

            newModal(){
                this.editmode = false;
                this.form.reset();
                $('#AddNew').modal('show');
            },

            deleteUser(id){
                swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                    if (result.value) {
                        
                        this.form.delete('api/user/'+id)
                        .then(() => {
                            swal.fire(
                                'Deleted!',
                                'Your file has been deleted.',
                                'success'
                            )
                            Fire.$emit('AfterCreate'); //beritahu si Fire bahwa ada event namanya "After Create", emit = tembaaaakkk
                        })
                        .catch(()=>{
                            swal('Failed!','There something wrong','warning')
                        })

                            
                    }
                })
            },
            
            editUser(){
                //console.log('editing data...');
                this.$Progress.start();
                console.log('mulai update');
                this.form.put('api/user/'+this.form.id)
                .then(() => {
                    //success
                    $('#AddNew').modal('hide');
                    Fire.$emit('AfterCreate'); //beritahu si Fire bahwa ada event namanya "After Create", emit = tembaaaakkk
                    swal.fire(
                        'Update!',
                        'Your info has been update.',
                        'success'
                        )
                    
                    this.$Progress.finish();
                            
                })
                .catch(() => {
                    //error
                     swal(
                        'Update!',
                        'There is a problem updating data.',
                        'warning'
                        )
                    this.$Progress.fail();
                });
            },

            createUser(){
                this.$Progress.start()
                this.form.post('api/user')
                .then(() => { //jika request berhassil
                    Fire.$emit('AfterCreate'); //beritahu si Fire bahwa ada event namanya "After Create", emit = tembaaaakkk
                    toast.fire({
                        type: 'success',
                        title: 'Successfully Registered'
                    });
                    $('#AddNew').modal('hide');
                    this.$Progress.finish();
                })
                .catch(() => { //jika request tidak berhasil,error,dll
                    swal('Failed!','There something wrong','warning')
                })

                    
            },
            loadUser(){
                
                if(this.$gate.isAdminOrAuthor()){
                    axios.get("api/user").then(({ data }) => (this.users = data));
                }
                
                
            }
        },
        
        created() {
            
            Fire.$on('searching',() => {
                let query = this.$parent.search;
                axios.get("api/findUser?q="+query)
                .then((data) => {
                    this.users = data.data;
                })
                .catch(() => {

                })
            })

            this.loadUser();
            
            // listener si Fire yang kalo tau ada event AfterCreate, bakal manggil fungsi LoadUser
            Fire.$on('AfterCreate', () =>{
                this.loadUser();
            });
            //setInterval(() => this.loadUser(),3000);
        }
    }
</script>
