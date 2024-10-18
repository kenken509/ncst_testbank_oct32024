<template>
    <div class="flex justify-center items-center h-[100vh] text-[100px] text-green-900">
     Your ip address is:  {{ ipAddress }}
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import axios from 'axios';
  
  const ipAddress = ref('');
  
  onMounted(async () => {
    try {
      const response = await axios.get('https://api.ipify.org/?format=json');
      ipAddress.value = response.data.ip;
    } catch (error) {
      console.error('Error fetching IP:', error);
    }
  });
  </script>
  