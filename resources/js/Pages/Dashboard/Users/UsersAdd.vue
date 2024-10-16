<template>
    <DashboardLayout>
        <div class="flex items-center justify-between border-bot-only py-2 ">
            <span class="text-[20px] font-bold text-gray-500">Add User Page</span> 
            <div class="relative">
                <input v-model="searchField" type="text" placeholder="search" class="rounded-md">
                <svg class="absolute top-3 right-2 w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                </svg>
            </div> 
        </div>{{ addNewUserForm.errors.email }}
        <div v-if="$page.props.flash.success" >{{ successMessage($page.props.flash.success) }} </div>
        <div v-if="$page.props.flash.error" >{{ errorMessage($page.props.flash.error) }} </div>
        <div v-if="addNewUserForm.errors.email">{{errorMessage(addNewUserForm.errors.email)}}</div>
        
        <div class="w-full pt-20 flex flex-col justify-center items-center">
            <!-- <div v-if="$page.props.flash.success">
                 <span class="text-green-500">
                    {{ $page.props.flash.success }} 
                    {{ clearForm ()}}
                 </span>
                 
            </div> -->
            <form class="w-full md:w-[60%] border border-gray-500 p-5 rounded-md shadow-md " >
                <div class="flex items-center justify-between mb-6 border-b-2">
                  <span class=" font-semibold text-lg text-gray-500">User Information</span> 
                  <div class="flex space-x-2 items-center">
                    <span class=" font-semibold text-lg text-gray-500">Default Password: <span class="text-red-600">{{ data.defaultPw.password }}</span></span> 
                    <i class="pi pi-pencil text-green-500 hover:cursor-pointer" @click="editDefaultPassword"></i>
                  </div>
                  
                </div>
                 
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col align-items-center gap-3 mb-3 col-span-2 md:col-span-1 relative">
                        <label for="username" class="font-semibold w-6rem">Email </label>
                        <input v-model="addNewUserForm.email" type="email" placeholder="Enter email" class="flex-auto border border-gray-500 rounded " required/>
                        <span v-if="addNewUserValidatorEmail" class="absolute inset-y-20 text-red-500" >{{ addNewUserValidatorEmail }} </span> 
                    </div>
                    
                    <div class="flex flex-col align-items-center gap-3 mb-3 col-span-2 md:col-span-1 relative">
                        <label for="username" class="font-semibold w-6rem">Name</label>
                        <input v-model="addNewUserForm.name" type="text" placeholder="Enter name" class="flex-auto border border-gray-500 rounded " required/>
                        <span v-if="addNewUserValidatorName" class="absolute inset-y-20 text-red-500" >{{ addNewUserValidatorName }} </span> 
                    </div>
                    
                    <div class="flex flex-col align-items-center gap-3 mb-3 col-span-2 md:col-span-1 relative">
                        <label for="role" class="font-semibold w-6rem">Role</label>
                        <select v-model="addUserRole" id="role" class="w-full rounded border-gray-500" required>
                            <option value="" selected hidden>Select a role</option>
                            <option v-for="(role, index) in roles" :key="index">{{ role }}</option>
                        </select>
                        <span v-if="addNewUserValidatorRole" class="absolute inset-y-20 text-red-500 absolute" >{{ addNewUserValidatorRole }} </span> 
                    </div>
                    
                    <div  class="flex flex-col align-items-center gap-3 mb-3 col-span-2 md:col-span-1 relative  "  >
                        
                        <label for="departmentName" class="font-semibold w-6rem">Department </label>
                        <select v-model="addUserDepartment"  id="departmentName" class="w-full rounded border-gray-500 " required :class="{'pointer-events-none':isAdmin || !addUserRole, ' opacity-40':isAdmin || !addUserRole, 'bg-gray-300':isAdmin || !addUserRole}"  >
                            <option value="" selected hidden>Select a department</option>
                            <option v-for="dep in data.departments" :key="dep.id" :value="dep">{{ dep.name }}</option>
                        </select>
                        <span v-if="addNewUserValidatorDep" class="absolute inset-y-20 text-red-500 absolute" >{{ addNewUserValidatorDep }} </span> 
                    </div>
                   
                    <div v-if="isFaculty && hasDivision" class="flex flex-col align-items-center gap-3 mb-3 col-span-2 relative">
                        <label for="departmentName" class="font-semibold w-6rem">Division</label>
                        <select  v-model="addUserDivision"  id="departmentName" class="w-full rounded border-gray-500" required>
                            <option value="" selected hidden>Select a division</option>
                            <option v-for="div in addUserDepartment.divisions" :key="div.id" :value="div">{{ div.name }}</option>
                        </select>
                        <span v-if="addNewUserValidatorDiv" class="absolute inset-y-20 text-red-500 absolute" >{{ addNewUserValidatorDiv }} </span> 
                    </div>
                </div>
                <!-- isfaculty = {{ isFaculty }} | hasdivision = {{ hasDivision }} || addUserDepartment = {{ addUserDepartment.name }} -->
                <div class="mt-4">
                    <button @click="submitNewUser" type="button" :disabled="addNewUserForm.processing" class="w-full btn-primary " >Save</button>
                </div>
                <div>
                    <button type="button" class="w-full btn-primary" @click="handleImportExcelModalOpen">Import Excel File</button>
                </div>
            </form>
           <!-- email: {{ addNewUserForm.email }} || name: {{ addNewUserForm.name }} || role: {{ addUserRole }} || department: {{ addUserDepartment.id }} || division {{ addUserDivision.id }} -->
          
        </div>    
        
        <Dialog v-model:visible="editPasswordModalOpen" modal  :style="{ width: '50rem' }">
            <form>
                <div class="flex flex-col align-items-center gap-3 mb-3 col-span-2 md:col-span-1 relative">
                    <label for="defPassword" class="font-semibold w-6rem">New Default Password</label>
                    <input v-model="newPassword" type="text" id="defPassword" placeholder="Enter new password" class="flex-auto border border-gray-500 rounded " required/>
                    <!-- <span v-if="addNewUserValidatorName" class="absolute inset-y-20 text-red-500" >{{ addNewUserValidatorName }} </span>  -->
                </div>
                
            </form>
            <div class="mt-4">
                <button @click="updateNewDefaultPassword" type="button" :disabled="addNewUserForm.processing" class="w-full btn-primary " >Save</button>
            </div>
        </Dialog>


        <Dialog v-model:visible="importModalOpen" modal header="Import Excel File"  :style="{ width: '50rem' }">
            
            <div>
                <hr/>
            </div>
            <div v-if="handleImportExcelError" class="pt-2" >
                <span class="text-red-500">{{ handleImportExcelError }}</span>
            </div>
            <div class="mt-4 relative">
                <form @submit.prevent="submitImportExcel">
                    <div class="pb-2 flex flex-col space-y-2">
                        
                        <label class="font-bold" for="depSelect">Department: </label>
                        <select v-model="importUserSelectedDepartment"  class="rounded-lg hover:cursor-pointer" id="depSelect" required>
                            <option value="" hidden selected>Select a department</option>
                            <option v-for="dep in data.departments" :key="dep.id" :value="dep">
                                {{ dep.name }}
                            </option>
                        </select>
                    </div>
                    <div class="pb-2 flex flex-col space-y-2" v-if="importUserHasDivision">
                        <label class="font-bold">Division:</label>
                        <select v-model="importUserSelectedDivision"  class="rounded-lg hover:cursor-pointer w-full" :required="importUserHasDivision">
                            <option value="" hidden selected>Select a division</option>
                            <option v-for="div in importUserSelectedDepartment.divisions" :value="div">
                                {{ div.name }}
                            </option>
                        </select>
                    </div>
                    <div class="pb-2 flex flex-col space-y-2">
                        <label class="font-bold">Excel File: </label>
                        <input  type="file" ref="fileInput" accept=".xls, .xlsx" @change="handleImportExcelFileChange" required/>
                    </div>
                    
                    <div>
                        <button type="submit" class="w-full btn-primary" > Import </button>
                    </div>
                </form>
                <button :disabled="isDownloading" type="button" class="w-full btn-primary flex items-center justify-center gap-2" @click="downloadExcelFormat" >
                    <i class="pi pi-arrow-circle-down"></i>
                    Excel Format 
                </button>
            </div>
            <ProgressSpinner v-if="isLoading"  class="absolute top-1/4 left-1/2 transform -translate-x-1/2 -translate-y-1/2"  />
            
        </Dialog>
    </DashboardLayout>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '../DashboardLayout.vue';
import {ref, watch} from 'vue'
import Swal from 'sweetalert2/dist/sweetalert2.js'


const searchField = ref('')


const data = defineProps({
    departments:Array,
    defaultPw:Object,
})

const addNewUserForm = useForm({
    email:'',
    name:'',
    role:'',
    department:'',
    division_id:'',
})




const addNewUserValidatorEmail = ref('')
const addNewUserValidatorName = ref('')
const addNewUserValidatorRole = ref('')
const addNewUserValidatorDep = ref('')
const addNewUserValidatorDiv = ref('')

const addUserRole = ref('');
const addUserDepartment = ref('');
const addUserDivision = ref('');

const isAdmin = ref(false)
const isDepHead = ref(false)
const isFaculty = ref(false);
const hasDivision = ref(false);

const roles = ref([
    'admin',
    'co-admin',
    'department head',
    'faculty',
])

function resetValidationError(){
    addNewUserValidatorEmail.value = ''
    addNewUserValidatorName.value = ''
    addNewUserValidatorRole.value = ''
    addNewUserValidatorDep.value = ''
    addNewUserValidatorDiv.value = ''
}

watch(addUserRole,(val)=>{
    
    if(val === 'admin' || val ==='co-admin')
    {
       
        isAdmin.value = true // disable department
        isDepHead.value = false
        isFaculty.value = false
        hasDivision.value = false;
        addUserDepartment.value =''
        addUserDivision.value = ''
        resetValidationError();
    }
    else
    {
        isAdmin.value = false 
    }

    if(val === 'department head')
    {
       
        
        isDepHead.value = true;
        isAdmin.value = false;
        isFaculty.value = false;
        hasDivision.value = false;
        addUserDepartment.value = ''
        addUserDivision.value = ''
        resetValidationError();
    }

    if(val === 'faculty')
    {
       
        isFaculty.value = true;
        isAdmin.value = false;
        isDepHead.value = false
        addUserDepartment.value =''
        resetValidationError();
        
    }
    else
    {
       
        isFaculty.value = false;
        hasDivision.value = false;
        addUserDivision.value = ''
    }
})

watch(addUserDepartment,(val)=>{
    
    // Check if 'divisions' property exists and it's not empty
    if (val && Array.isArray(val.divisions) && val.divisions.length > 0) {
        hasDivision.value = true; // Set hasDivision to true
    } else {
        hasDivision.value = false; // Set hasDivision to false
    }
    
})

const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
}

const submitNewUser = ()=> {

addNewUserValidatorEmail.value = !addNewUserForm.email ? 'Email is required': !isValidEmail(addNewUserForm.email) ? 'Invalid email format' : ''
addNewUserValidatorName.value = !addNewUserForm.name ? 'Name is required': ''
addNewUserValidatorRole.value = !addUserRole.value ? 'Role is required': ''


if(addUserRole.value === 'department head')
{
    
    
    if(!addUserDepartment.value)
    {
        
        addNewUserValidatorDep.value = 'Department is required';
    }
    else
    {
        
        addNewUserValidatorDep.value = ''
    }
}

if(addUserRole.value === 'faculty')
{
    if(!addUserDepartment.value)
    {
        addNewUserValidatorDep.value = 'Department is required';
    }
    else
    {
        addNewUserValidatorDep.value = ''
        if(hasDivision.value)
        {
            if(!addUserDivision.value)
            {
                addNewUserValidatorDiv.value = 'Division is required'
            }
            else
            {
                addNewUserValidatorDiv.value = ''
            }
        }
        else
        {
            addNewUserValidatorDiv.value = ''
        }
    }
}

    if(addNewUserValidatorEmail.value === '' && addNewUserValidatorName.value === '' &&  addNewUserValidatorRole.value === '' && addNewUserValidatorDep.value === '' && addNewUserValidatorDiv.value === '')
    {
        
        //email: {{ addNewUserForm.email }} || name: {{ addNewUserForm.name }} || role: {{ addUserRole }} || department: {{ addUserDepartment.id }} || division {{ addUserDivision.id }}
        addNewUserForm.role = addUserRole.value;
        addNewUserForm.department = addUserDepartment.value.id;
        addNewUserForm.division_id = addUserDivision.value.id;
        addNewUserForm.post(route('user.store'));
          
    }

}

// sweet alert logic
function successMessage(message)
    {
        Swal.fire({
            title:'Success',
            text:message,
            icon:'success',
            allowOutsideClick:false,
            allowEscapeKey:false,
        }).then((result)=>{
            if(result.isConfirmed)
            {
                location.reload();
                
            }
        })
    }
    
    function errorMessage(message) {
        Swal.fire({
            icon: "error",
            title: "Oops...",
            text: message + '!',
            allowOutsideClick:false,
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload();
            }
        })
    }

    
    // edit password logic  ***************************************************
    const editPasswordModalOpen = ref(false);
    const inputPassword = ref('');
    const editDefaultPassword = ()=> {
        editPasswordModalOpen.value = !editPasswordModalOpen.value;
    }

    const editPasswordForm = useForm({
        password:'',
    })

    const newPassword = ref('');
    const updateNewDefaultPassword = ()=>{
        console.log(newPassword.value);
        if(!validatePassword(newPassword.value))
        {
            errorMessage('Password must be at least 8 characters long and include uppercase, lowercase, digit, and special character.');
            editPasswordModalOpen.value = false;
            return;
        }
        // 
        editPasswordForm.password = newPassword.value;
        editPasswordModalOpen.value = false
        editPasswordForm.post(route('default.password.update'));
        
    }
    

    function validatePassword(password) {
    // Custom validation function for password strength
    if (password.length < 8) {
      return false;
    }
  
    let hasUpperCase = false;
    let hasLowerCase = false;
    let hasDigit = false;
    let hasSpecialChar = false;
  
    for (let char of password) {
      if (char >= 'A' && char <= 'Z') {
        hasUpperCase = true;
      } else if (char >= 'a' && char <= 'z') {
        hasLowerCase = true;
      } else if (char >= '0' && char <= '9') {
        hasDigit = true;
      } else {
        // Assuming special characters are anything not alphanumeric
        hasSpecialChar = true;
      }
    }
  
    return hasUpperCase && hasLowerCase && hasDigit && hasSpecialChar;
  }
 
  const importModalOpen = ref(false)
  const handleImportExcelModalOpen = ()=>{
    importModalOpen.value = !importModalOpen.value
  }

//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
const handleImportExcelError = ref('')
const fileInput = ref(null); // Reference to the file input
const importExcelForm = useForm({
    department_id:'',
    division_id:'',
    file:null,
})
const handleImportExcelFileChange = (event)=> 
{
    const file = event.target.files[0]; // Get the first file
    handleImportExcelError.value = ''; // Reset error message
    
    
    if (file) 
    {
        let validExtensions = ['xls', 'xlsx'];
        let fileExtension = file.name.split('.').pop().toLowerCase();

         // Check if the file extension is valid
        if (!validExtensions.includes(fileExtension)) 
        {
            handleImportExcelError.value = 'Please upload a valid Excel file (.xls or .xlsx).';
            fileInput.value.value = ''; // Reset the input
            return;
        }

        
    }
   
    importExcelForm.file = file

    //importExcelForm.post('user.import.excel')

}
const isLoading = ref(false)
const submitImportExcel = ()=> {
    isLoading.value = true
    importExcelForm.post(route('user.import.excel'),{
        onSuccess: ()=>{
            isLoading.value = false;
            importModalOpen.value = false;
        }
    })
}
  
const importUserSelectedDepartment = ref('');
const importUserSelectedDivision = ref('');

const importUserHasDivision = ref(false)
watch(importUserSelectedDepartment, (dep)=>{

    if(dep.divisions.length)
    {
       importUserHasDivision.value = true; 
    }
    else
    {
        importUserHasDivision.value = false
    }

    importExcelForm.department_id = dep.id
}) 
 
 watch(importUserSelectedDivision,(div)=>{
    importExcelForm.division_id = div.id
 })

 const isDownloading = ref(false);
 const downloadExcelFormat = async()=>{
    isDownloading.value = true;

    try
    {
        const url = route('user.download.excel-format');
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'user_import_format.xlsx');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    catch(error)
    {
        console.error('Download failed:', error);
    }
    finally
    {
        isDownloading.value = false;
    }
 }
</script>