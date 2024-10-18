

<template>
    <DashboardLayout>
        <div class="flex items-center justify-between border-bot-only py-2 mb-4">
            <span class="text-[20px] font-bold text-gray-500">User Activities</span> 
            
        </div>
        <div>
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 rounded-t-md overflow-hidden dark:text-gray-400">
                <thead class="text-xs text-gray-200 uppercase bg-blue-900 dark:bg-gray-700 dark:text-gray-400 ">
                    <tr>
                        <th scope="col" class="px-6 py-3">No.</th>
                        <th scope="col" class="px-6 py-3">email</th>
                        <th scope="col" class="px-6 py-3">Action</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Status Message</th>
                        <th scope="col" class="px-6 py-3">Date/Time</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(log,index) in logs" :key="index" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-gray-100 hover:cursor-pointer">
                        <td class=" align-middle px-6 py-3 text-black  ">
                            {{ index+1 }}
                        </td>
                        <td class=" align-middle px-6 py-3 text-black ">
                            {{ log.user.email }}
                        </td>
                        <td class=" align-middle px-6 py-3 text-black">
                           {{ log.action }}
                        </td>
                        <td class=" align-middle px-6 py-3 text-black ">
                          {{ log.status }}
                        </td>
                        <td class=" align-middle px-6 py-3 text-black ">
                            {{ log.status_message }}
                        </td>
                        <td class=" align-middle px-6 py-3 text-black ">
                            {{ formatDate(log.created_at) }}
                        </td>
                       
                    </tr>
                    
                </tbody>
                
            </table>
        </div>
        
    </DashboardLayout>
   
</template>

<script setup>
import DashboardLayout from '../DashboardLayout.vue';

const data = defineProps({
    logs:Array,
})

function formatDate(isoString) {
    const date = new Date(isoString);
    
    const options = {
        month: '2-digit',
        day: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    };
    
    // Use toLocaleString to format the date
    const formattedDate = date.toLocaleString('en-US', options);
    
    // Return formatted date
    return formattedDate.replace(',', ''); // Clean up comma
}
</script>