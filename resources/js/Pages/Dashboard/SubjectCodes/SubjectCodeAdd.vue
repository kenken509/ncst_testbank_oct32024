<template>
<DashboardLayout>
    <div class="flex items-center justify-between border-bot-only py-2 ">
        <span class="text-[20px] font-bold text-gray-500">Add Subject Codes Page </span> 
        
    </div>
    <!-- hasDivision || {{ hasDivision }} || selected dep: {{ selectedDepartment.name }} || selected Div: {{ selectedDivision }} || Description:  {{ form.description.length }} -->
    <div class="w-full flex justify-center">
        <form @submit.prevent="submitConfirmation" class="w-full md:w-[50%] border border-gray-500 rounded-md p-4 shadow-md mt-4 md:mt-[50px] " >
            
            <div v-if="form.errors.name">{{ errorMessage(form.errors.name) }}</div>
            <div class="flex w-full flex-col ">
                <span class="w-full border-b border-gray-300 mb-4 ">
                    Information
                </span>
                <label for="subCode" class="my-2 text-lg font-semibold text-gray-600">Subject Code :</label>
                <input v-model="form.name" type="text" id="subCode" placeholder="Enter subject code" class="rounded rounded-lg border border-blue-700 my-2" maxlength="15" required/>
                
                <label for="department" class="my-2 text-lg font-semibold text-gray-600">Department :</label>
                <select v-model="selectedDepartment" id="department" class=" border border-blue-500 text-gray-500 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 my-2 hover:cursor-pointer" required>
                    <option value="" selected hidden >Select a department</option>
                    <option v-for="dep in data.departments" :key="dep.id" :value="dep" class=" text-lg ">
                        {{ dep.name }}
                    </option>
                </select>

                <label :class="{'hidden':!hasDivision}" for="department" class="my-2 text-lg font-semibold text-gray-600">Division :</label>
                <select  v-model="selectedDivision" 
                    :class="{'hidden':!hasDivision}"
                    class=" border border-blue-500 text-gray-500 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 my-2 hover:cursor-pointer"
                    :required="hasDivision"
                    >
                    <option value="" hidden selected  >Select a division</option>
                    <option v-for="div in selectedDepartment.divisions" :key="div.key" :value="div">
                        {{ div.name }}
                    </option>
                </select>
                
                <label for="description" class="my-2 text-lg font-semibold text-gray-600">Description : </label>
                <span v-if="descriptionLengthValidator" class="text-red-500">{{ descriptionLengthValidator }}</span>
                <textarea required v-model="form.description" id="description" name="subDescription" rows="4" cols="10" class="bg-gray-50 border border-blue-500 text-gray-500 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 my-2 hover:cursor-pointer">

                </textarea>
                <!-- <label for="departments" class="my-2 text-lg font-semibold text-gray-600" >Department :</label>
                <select v-model="form.departmentId"  id="countries" class="bg-gray-50 border border-blue-500 text-gray-500 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 my-2 hover:cursor-pointer" required>
                    <option value="" selected hidden>Select a department</option>
                    <option :value="dep.id" v-for="dep in data.existingDepartment" :key="dep.id" class="text-gray-800">
                        {{ dep.name }}
                    </option>
                </select> -->
                
                <button   type="submit" class="btn-primary " :disabled="form.processing">Save</button>
                <button   type="Button" class="btn-primary " @click="handleopenExcelImportModalButtonClicked">Import Excel File</button>
            </div>
    </form>
    </div>

    <Dialog v-model:visible="openExcelImportModal" modal header="Import Excel File" :style="{ width: '30rem' }">
        <div><hr class="border-black"></div>
        
        <form @submit.prevent="importConfirmation">
            <div class="mt-2" >
                <label for="department" class="font-bold" >Department : </label>
                <select v-model="importSelectedDepartment" id="department" class="w-full rounded-md my-1" required>
                    <option value="" selected hidden >Select a department</option>
                    <option v-for="dep in data.departments" :key="dep.id" :value="dep">
                        {{ dep.name }}
                    </option>
                </select>
            </div>
            
            <div v-if="importDepartmentHasDivision">
                <label for="division" class="font-bold" >Division : </label>
               <select v-model="importSelectedDivision" for="division" class="w-full rounded-md my-1" :required="importDepartmentHasDivision">
                    <option value="" selected hidden >Select a division</option>
                    <option v-for="div in importSelectedDepartment.divisions" :value="div">
                        {{ div.name }}
                    </option>
               </select>
            </div>
            <div class="flex flex-col gap-2  mt-2  ">
                <label for="excelInputFile" class="font-bold" >Excel File: </label>
                <input type="file" id="excelInputFile" ref="fileInput" accept=".xlsx" @change="handleImportExcelInputChange" required>
                <span class="text-red-500 px-1">{{ handleImportExcelError }}</span>
            </div>
           
            
            <div class="my-2"><hr class="border-black "></div>
            <div class="mt-2">
                <button type="submit" class="btn-primary w-full">Import</button>
            </div>
            <button :disabled="isDownloading" type="button" class="w-full btn-primary flex items-center justify-center gap-2" @click="downloadExcelFormat" >
                <i class="pi pi-arrow-circle-down"></i>
                Excel Format 
            </button>
        </form>
        
    </Dialog>
</DashboardLayout>

</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '../DashboardLayout.vue';
import {ref, watch} from 'vue'
import Swal from 'sweetalert2/dist/sweetalert2.js'



const form = useForm({
    name:'',
    description:'',
    department_id:'',
    division_id:'',
})


const data = defineProps({
    departments:Array,
})

//const subjectDescription = ref('')
const selectedDepartment = ref('')
const selectedDivision = ref('')
const hasDivision = ref(false);
const descriptionLengthValidator = ref('')

watch(selectedDepartment,(val)=>{

    if(val.divisions.length > 1)
    {
        hasDivision.value = true
    }
    else
    {
        
        hasDivision.value = false
        selectedDivision.value =''
    }
    
})

const validator = ()=>{
    if(form.description.length > 70)
    {
        
        descriptionLengthValidator.value = "Description could not exceed 50 characters."
    }
    else
    {
        
        descriptionLengthValidator.value = ''
    }
}

const submitConfirmation = ()=> 
    { 
        validator();

        if(descriptionLengthValidator.value === '')
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
                    
                    
                    form.department_id = selectedDepartment.value.id
                    form.division_id = selectedDivision.value.id

                    form.post(route('subject.codes.store'));
                    
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
    division_id:'',
})
const openExcelImportModal = ref('');
const handleopenExcelImportModalButtonClicked = ()=>{
    openExcelImportModal.value = !openExcelImportModal.value
}

const importConfirmation = ()=> 
{ 
    openExcelImportModal.value = false
    Swal.fire({
        title: "Are you sure?",
        text: "----",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Proceed!",
        allowOutsideClick:false,
        allowEscapeKey:false,
        }).then((result) => {
            if(result.isConfirmed)
            {
                // const deleteUrl = route('user.delete',{id: userId })

                // router.delete(deleteUrl);
                // file:'',
                // department_id:'',
                // division_id:''


                

                importExcelForm.post(route('subject.codes.import', {
                    onSuccess:()=>{
                        console.log('successful import');
                    }
                }))
            }

            if(result.isDismissed)
            {
                // Swal.fire({
                //     title:'Canceled',
                //     text:'Your action was canceled!',
                //     icon:'error',
                //     confirmButtonColor: '#3085d6',
                // })
                openExcelImportModal.value = true;
                
            }
            
    });
} 
    
const fileInput = ref(null)
const handleImportExcelError = ref('');  
const importSelectedDepartment = ref('');  
const importSelectedDivision = ref('');

const importDepartmentHasDivision = ref(false)

watch(importSelectedDepartment, (dep)=>{

    importExcelForm.department_id = dep.id;

    if(dep.divisions)
    {
        if(dep.divisions.length)
        {
            importDepartmentHasDivision.value = true;
        }
        else
        {
            importDepartmentHasDivision.value = false;
        }
    }
    
})

watch(importSelectedDivision, (div)=>{
    importExcelForm.division_id = div.id;
})

const handleImportExcelInputChange = (event)=>{
    
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

//download excel format
const isDownloading = ref(false);
const downloadExcelFormat = async () => {
  // Disable the button
  isDownloading.value = true;

  try {
    const url = route('subject.codes.excel-format'); // Your download URL
    // Create a temporary anchor element for the download
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'subject_code_import_format.xlsx');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    console.error('Download failed:', error);
  } finally {
    // Re-enable the button after the download process
    isDownloading.value = false;
  }
};
</script>