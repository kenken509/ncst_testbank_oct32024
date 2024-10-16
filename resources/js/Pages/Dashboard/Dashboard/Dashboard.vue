<template>

    <DashboardLayout>
        <!--users count-->
        <div class="flex items-center justify-between border-bot-only py-2 mb-4">
            <span class="text-[20px] font-bold text-gray-500">Dashboard </span> 
            
        </div>
        <div class="flex h-full w-full justify-center items-center mt-8"> 
            <div class="w-full grid grid-cols-8 gap-2">
                <div class="col-span-8 lg:col-span-2 w-full flex flex-col  gap-2 border border-gray-500 rounded-md p-6 shadow-md bg-blue-100">
                    <div class="flex items-center gap-2">
                        <i class="pi pi-users"></i>
                        <span>Users</span>
                    </div>
                    <span class="text-[30px] font-semibold mt-4">{{ usersCount }}</span>
                </div>
               
                
                <div class="col-span-8 lg:col-span-2 w-full flex flex-col  gap-2 border border-gray-500 rounded-md p-6 shadow-md bg-blue-100">
                    <div class="flex items-center gap-2">
                        <i class="pi pi-users"></i>
                        <span>Department Heads</span>
                    </div>
                    <span class="text-[30px] font-semibold mt-4">{{ depHeadCount }}</span>
                </div>
                <div class="col-span-8 lg:col-span-2 w-full flex flex-col  gap-2 border border-gray-500 rounded-md p-6 shadow-md bg-blue-100">
                    <div class="flex items-center gap-2">
                        <i class="pi pi-users"></i>
                        <span>Faculties</span>
                    </div>
                    <span class="text-[30px] font-semibold mt-4">{{ facultyCount }}</span>
                </div>
                <div class="col-span-8 lg:col-span-2 w-full flex flex-col  gap-2 border border-gray-500 rounded-md p-6 shadow-md bg-blue-100">
                    <div class="flex items-center gap-2">
                        <i class="pi pi-question-circle"></i>
                        <span>Questions</span>
                    </div>
                    <span class="text-[30px] font-semibold mt-4">{{ questionsCount }}</span>
                </div>
            </div>
        </div>

        
        <!--users count-->

        <div class="grid grid-cols-2 gap-2 w-full justify-between ">
            
            <div class=" col-span-2 lg:col-span-1  overflow-x-auto shadow-md sm:rounded-lg mt-10  border-gray-500 border  ">
                <div class="  flex justify-center border rounded-t-md bg-blue-900 border-gray-500 text-gray-800 w-full">
                    <span class="text-[20px] font-semibold text-white p-1">LOWEST CONTRIBUTIONS</span>
                </div>
                <div class="w-full h-auto overflow-x-auto ">
                    <table class="min-w-full h-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 ">
                        <thead class="text-xs text-gray-200 uppercase bg-blue-900 dark:bg-gray-700 dark:text-gray-400">
                            <tr scope="row">
                                <th scope="col" class="px-6 py-3">No.</th>
                                <th scope="col" class="px-6 py-3">User</th>
                                <th scope="col" class="px-6 py-3">Email</th>
                                <th scope="col" class="px-6 py-3">Role</th>
                                <th scope="col" class="px-6 py-3">Questions</th>
                            </tr>
                        </thead>
                        <tbody v-for="(user,index) in userQuestionCount" :key="index">
                            <tr scope="row">
                                <td scope="col" class="px-6 py-3 text-gray-800">
                                    {{ index+1 }}
                                </td>
                                <td scope="col" class="px-6 py-3 text-gray-800">
                                    {{ user.name }} 
                                </td>
                                <td scope="col" class="px-6 py-3 text-gray-800">
                                {{ user.email }}
                                </td>
                                <td scope="col" class="px-6 py-3 text-gray-800">
                                {{ user.role }}
                                </td>
                                <td scope="col" class="px-6 py-3 text-gray-800">
                                    {{ user.questions_count }}
                                </td>
                            </tr>
                            
                            
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-10 col-span-2 lg:col-span-1 border-gray-300 w-full rounded-lg border border-1 border-gray-500 shadow-md">
                    <div class="w-full flex justify-center bg-blue-900 py-2 border-b-[1px] border-gray-500 rounded-t-md">
                        <span class=" text-white font-bold ">
                            EMPTY SUBJECT CODES
                        </span>
                    </div>
                    
                    <table class="w-full table-fixed  border-green-400">
                        <thead class="bg-blue-900">
                            <tr scope="row" class="text-white w-full">
                                <th scope="col" class="px-6 py-3">Department Name</th>
                                <th scope="col" class="px-6 py-3">Codes</th>
                                <th scope="col" class="px-6 py-3">View</th>
                            </tr>
                        </thead>
                        <tbody v-for="department in emptySubjectCodes" :key="department.id"  class="rounded-md">

                            <tr v-if="department.subject_codes.length" scope="row text" class=" ">
                                <td scope="col" class="px-6 py-3 text-gray-800 border text-center">
                                    {{department.name}}
                                </td>
                                <td scope="col" class="px-6 py-3 text-center text-gray-800 border">
                                    {{department.subject_codes.length}}
                                </td>
                                <td scope="col" class="px-6 py-3 text-center text-gray-800 border hover:cursor-pointer" @click="handleOpenCodesModal(department.id)">
                                    <i class="pi pi-eye text-blue-800 text-lg hover:text-blue-400" ></i>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                
                
            </div>
           

            <Dialog v-model:visible="openCodesModal" modal header=" Empty Codes List" title="Empty Codes List" :style="{ width: '20rem'}">
                <div><hr></div>
                
                <div v-for="(code, index) in selectedDepartmentCodeList[0].subject_codes" :key="index" class="mt-4">
                    <span>{{ index+1 }} . &nbsp;</span>
                    <span>
                        {{ code.name }}
                    </span>
                </div>
            </Dialog>
            <!-- <div class="w-full max-h-[500px] flex justify-center items-center border border-gray-500 mt-10 rounded-lg shadow-md">
                <Pie :data="chartData" :options="chartOptions" class="p-2" />
            </div> -->
        </div>
        

        
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '../DashboardLayout.vue';
import { Pie } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale } from 'chart.js'
import {computed, ref} from 'vue'

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale)

const data = defineProps({
    usersCount:Number,
    adminsCount:Number,
    depHeadCount:Number,
    facultyCount:Number,
    questionsCount:Number,
    userQuestionCount:Array,
    coAdminsCount:Number,
    emptySubjectCodes:Array,
})

// Compute chart data based on props
const chartData = computed(() => ({
  labels: ['Admins', 'Co-admins', 'Dep. Heads', 'Faculties'],
  datasets: [
    {
      label: 'Counts',
      backgroundColor: ['#F2C80C', '#60AEE4', '#2266C3', '#1B2C5D' ],
      data: [
        data.adminsCount,
        data.depHeadCount,
        data.facultyCount,
        data.coAdminsCount,
      ],
    },
  ],
}))

// Chart options
const chartOptions = computed(() => ({
  responsive: true,
  plugins: {
    legend: {
      position: 'top',
    },
    tooltip: {
      callbacks: {
        label: function (tooltipItem) {
          return `${tooltipItem.label}: ${tooltipItem.raw}`;
        },
      },
    },
  },
}))


const openCodesModal = ref(false)
const selectedDepartmentCodeList = ref([]);
const handleOpenCodesModal = (id)=>{
    openCodesModal.value = !openCodesModal.value
    selectedDepartmentCodeList.value = data.emptySubjectCodes.filter((dep)=> dep.id === id);

    //console.log(selectedDepartmentCodeList.value)
}
</script>
