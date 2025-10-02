
<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { Form } from '@inertiajs/vue3';
import BigButton from '@/Components/BigButton.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const creatingGroup = ref(false);

onMounted(() => {
    
});

</script>

<template>
    <div>
        <BigButton 
            @click="creatingGroup = !creatingGroup" 
        >
            Create A Group
        </BigButton>
        <Modal :show="creatingGroup" @close="creatingGroup = false">
            <div class="p-6 flex flex-col">
                <h2
                    class="text-lg font-medium text-gray-900 text-center sm:text-left"
                >
                    Create A New Group
                </h2>
                <Form 
                    :action="route('group.store')" 
                    method="post" 
                    #default="{ errors }"
                    :transform="data => ({ 
                        ...data, 
                        user_id: usePage().props.auth.user.id 
                    })"
                    @success="creatingGroup = false"
                    >
                    <label 
                        for="name" 
                        class="hidden" 
                    >
                        Enter a group name
                    </label>
                    <TextInput
                        type="text" 
                        name="name"
                        id="name" 
                        aria-labelledby="name"
                        placeholder="Enter a group name..."
                        class="mt-4 w-full"
                    /> 
                    <div>
                    <InputError v-if="errors.name" class="mt-2 text-center sm:text-left" :message="errors.name" />
                        <div class="flex flex-row mt-4 justify-center sm:justify-end">
                            <SecondaryButton 
                                @click="creatingGroup = false"
                                type="button"
                            >
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton
                                class="ms-3"
                                type="submit"
                            >
                                Save
                            </PrimaryButton>
                        </div>
                    </div>
                </Form>
            </div>
        </Modal>
    </div>
</template>
