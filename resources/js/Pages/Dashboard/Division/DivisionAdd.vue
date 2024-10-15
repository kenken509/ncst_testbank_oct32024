<template>
    <DashboardLayout>
        <div class="flex items-center justify-between border-bot-only py-2 mb-4">
            <span class="text-[20px] font-bold text-gray-500">Add Division Page</span> 
            
        </div>
        
        <div v-if="$page.props.flash.success" >{{ successMessage($page.props.flash.success) }} </div>
        <div v-if="$page.props.flash.error" >{{ errorMessage($page.props.flash.error) }} </div>
        <div class="w-full flex justify-center mt-[100px]">
            <form @submit.prevent="addConfirmation">

                <div class="flex w-[100vw] flex-col  md:w-[400px]  border border-gray-500 rounded shadow-md px-4 pt-6">
                    <span class="border-b border-gray-300 mb-2 ">
                        Information
                    </span>
                    <label for="depName" class="my-2 text-lg font-semibold text-gray-600">Division Name :</label>
                    <input v-model="form.divisionName" type="text" id="depName" placeholder="Enter division name" class="rounded rounded-lg border border-blue-700 my-2" maxlength="30" required/>
                    
                    
                    <label for="departments" class="my-2 text-lg font-semibold text-gray-600" >Department :</label>
                    <select v-model="form.departmentId"  id="countries" class="bg-gray-50 border border-blue-500 text-gray-500 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 my-2 hover:cursor-pointer" required>
                        <option value="" selected hidden>Select a department</option>
                        <option :value="dep.id" v-for="dep in data.existingDepartment"  :key="dep.id" class="text-gray-800">
                            {{ dep.name }}
                        </option>
                    </select>
                    
                    <button  type="submit" class="btn-primary ">Save</button>
                    <button   type="Button" class="btn-primary " @click="handleopenExcelImportModalButtonClicked">Import Excel File</button>
                </div>
            </form>
        </div>

        <Dialog v-model:visible="openExcelImportModal" modal header="Import Excel File" :style="{ width: '30rem' }">
            <div ><hr class="border-black"></div>
           
            <form @submit.prevent="importConfirmation">
                <div class="my-2 space-y-2">
                    <label class="font-bold" for="department">Department: </label>
                    <select v-model="importExcelSelectedDepartment" class="w-full border-blue-300 rounded-md hover:cursor-pointer" id="department" required>
                        <option value="" hidden selected>Please select department</option>
                        <option v-for="dep in data.existingDepartment" :key="dep.id" :value="dep">
                            {{ dep.name }}
                        </option>
                    </select>
                </div>
                <div class="flex flex-col my-2 space-y-2">
                    <label class="font-bold" for="excelFile">Excel File: </label>
                    <input type="file" id="excelFile" ref="fileInput" accept=".xlsx" @change="handleExcelInputFileChange" required/>
                    <span v-if="handleImportExcelError" class="text-red-500">{{ handleImportExcelError }}</span>
                </div>
                <div><hr class="border-black"></div>
                <div class="mt-2">
                    <button class="btn-primary w-full">Import</button>
                </div>
            </form>
        </Dialog>
    </DashboardLayout>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '../DashboardLayout.vue';
import {ref, watch} from 'vue'
import Swal from 'sweetalert2'

const searchField = ref('')
const selectedDepartmentId = ref(null)
const data = defineProps({
    existingDepartment:Array,
})

watch(selectedDepartmentId,(val)=>{
    console.log('current selected depId: '+val)
})


const form = useForm({
    divisionName:'',
    departmentId:'',
})


const addConfirmation = ()=> 
    { 
        Swal.fire({
            title: "Are you sure?",
            text: "Are you sure you want to save this new division?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, save it!",
            allowOutsideClick:false,
            allowEscapeKey:false,
            }).then((result) => {
                if(result.isConfirmed)
                {
                    const createUrl = form.post(route('division.store')); 

                    //router.delete(deleteUrl);
                }

                if(result.isDismissed)
                {
                    Swal.fire({
                        title:'Canceled',
                        text:'Your action was canceled!',
                        icon:'error',
                        confirmButtonColor: '#3085d6',
                    })
                }
        });
    }  

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

    // import logic

    const importExcelForm = useForm({
        file:'',
        department_id:'',
    })
    const openExcelImportModal = ref(false);
    const importExcelSelectedDepartment = ref('');
    const handleopenExcelImportModalButtonClicked = ()=>{
        openExcelImportModal.value = !openExcelImportModal.value
    }

    const handleImportExcelError = ref('')
    const fileInput = ref(null)
    const handleExcelInputFileChange = (event)=>{
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

    }

    watch(importExcelSelectedDepartment, (dep)=>{
        
        importExcelForm.department_id = dep.id;
    })

    const importConfirmation = ()=> 
    { 
        openExcelImportModal.value = false;
        Swal.fire({
            title: "Are you sure?",
            text: "-----",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, save it!",
            allowOutsideClick:false,
            allowEscapeKey:false,
            }).then((result) => {
                if(result.isConfirmed)
                {
                   importExcelForm.post(route('division.import.excel'), {
                        onSuccess:()=>{
                            console.log('successful excel import');
                        }
                   })
                }

                if(result.isDismissed)
                {
                    // Swal.fire({
                    //     title:'Canceled',
                    //     text:'Your action was canceled!',
                    //     icon:'error',
                    //     confirmButtonColor: '#3085d6',
                    // })
                    openExcelImportModal.value = false
                    importExcelSelectedDepartment.value = '';
                }
        });
    }  
</script>

