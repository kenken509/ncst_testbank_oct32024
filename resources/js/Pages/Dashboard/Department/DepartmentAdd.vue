<template>
    <DashboardLayout>
        <div class="flex items-center justify-between border-bot-only py-2 mb-4">
            <span class="text-[20px] font-bold text-gray-500">Add Department Page</span> 
        </div>
        <!-- <div class="mt-4">
            <span class="font-semibold text-2xl mt-10 ">New Department</span>
        </div> -->
        <div v-if="$page.props.flash.error" >{{ errorMessage($page.props.flash.error) }} </div>
        <div v-if="form.errors.departmentName">{{errorMessage(form.errors.departmentName)}}</div>
       
        <div class="w-full flex flex-col items-center justify-center md:mt-[100px]">
            <div v-if="$page.props.flash.success" class="w-full flex justify-center p-2 ">
                <span class="text-green-400">{{ $page.props.flash.success }}</span>
            </div>
            <div class="flex flex-col align-items-center  mb-3  w-full   md:w-[400px] broder broder-gray-300 rounded-md shadow-md p-4 border border-gray-500">
                <span class="border-b border-gray-300 mb-2 ">
                    Information
                </span>
                <div>
                    <label for="username" class="font-semibold ">Department Name</label>
                    <input v-model="form.departmentName" type="text" placeholder="Enter department name" class="flex-auto border border-gray-500 rounded w-full" maxlength="50" required/>
                </div>
                <div class="mt-2">
                    <button @click="submitConfirmation" type="button" class="w-full btn-primary" >Save</button>
                    <button type="button" class="bg-blue-700 p-2 rounded-lg text-gray-100 w-full" @click="handleopenExcelImportModalButtonClicked">Import Excel File</button>
                </div>
                
            </div>  
            
        </div>
        
        <Dialog v-model:visible="openExcelImportModal" modal header="Import Excel File" :style="{ width: '30rem' }">
            <div><hr class="border-black"></div>
            <form @submit.prevent="importConfirmation">
                <div class="flex flex-col space-y-2 mt-4">
                    <label class="font-bold text-lg" for="excelInput">Excel File:</label>
                    <input type="file" ref="fileInput" accept=".xlsx" @change="handleExcelInputFileChange" required/>
                    <span v-if="handleImportExcelError" class="text-red-500">{{ handleImportExcelError }}</span>
                </div>
                <div class="my-2"><hr class="border-black"></div>
                <div>
                    <button class="btn-primary w-full" type="submit">Import</button>                   
                </div>
            </form>
            <!-- <button :disabled="isDownloading" type="button" class="w-full btn-primary flex items-center justify-center gap-2" @click="downloadExcelFormat" >
                    <i class="pi pi-arrow-circle-down"></i>
                    Excel Format 
                </button> -->
        </Dialog>
            
    </DashboardLayout>
</template>

<script setup>
import {ref} from 'vue'
import DashboardLayout from '../DashboardLayout.vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2/dist/sweetalert2.js'


const form = useForm({
    departmentName:''
})

    const submitConfirmation = ()=> 
    { 
        Swal.fire({
            title: "Submit Confirmation",
            text: "Are you sure?",
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
                    form.post(route('department.store'));
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
const toggleModal = ref(false)

// import logic
const importExcelForm = useForm({
    file:'',
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
                   importExcelForm.post(route('department.import.excel'), {
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