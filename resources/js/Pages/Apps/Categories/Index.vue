<template>
    <Head>
        <title>Kategori Material - MaterialTrack</title>
    </Head>

    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">

                    <div class="card border-0 rounded-3 shadow">

                        <div class="card-header d-flex justify-content-between align-items-center">

                            <span class="font-weight-bold">
                                <i class="fa fa-folder me-1"></i>
                                KATEGORI MATERIAL
                            </span>


                            <Link
                                v-if="hasAnyPermission(['categories.create'])"
                                href="/apps/categories/create"
                                class="btn btn-primary btn-sm"
                            >
                                <i class="fa fa-plus-circle me-2"></i>
                                NEW
                            </Link>


                        </div>


                        <div class="card-body">

                            <table class="table table-striped table-bordered table-hover">

                                <thead>
                                    <tr>
                                        <th scope="col">Nama</th>
                                        <th scope="col">Deskripsi</th>
                                        <th scope="col">Jumlah Item</th>
                                        <th scope="col" width="15%" class="text-center">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>


                                <tbody>


                                    <tr v-if="categories.data.length === 0">

                                        <td colspan="4" class="text-center">
                                            Tidak ada data.
                                        </td>

                                    </tr>


                                    <tr
                                        v-for="cat in categories.data"
                                        :key="cat.id"
                                    >

                                        <td>
                                            {{ cat.name }}
                                        </td>


                                        <td>
                                            {{ cat.description ?? '-' }}
                                        </td>


                                        <td>
                                            {{ cat.items_count }}
                                        </td>


                                        <td class="text-center">


                                            <Link
                                                v-if="hasAnyPermission(['categories.edit'])"
                                                :href="`/apps/categories/${cat.id}/edit`"
                                                class="btn btn-success btn-sm me-2"
                                                aria-label="Edit Kategori"
                                            >
                                                <i class="fa fa-pencil-alt" aria-hidden="true"></i>
                                            </Link>



                                            <button
                                                v-if="hasAnyPermission(['categories.delete'])"
                                                class="btn btn-danger btn-sm"
                                                aria-label="Hapus Kategori"
                                                @click="handleDelete(cat.id)"
                                            >

                                                <i class="fa fa-trash" aria-hidden="true"></i>

                                            </button>


                                        </td>


                                    </tr>


                                </tbody>


                            </table>


                            <Pagination 
                                :links="categories.links" 
                                align="end"
                            />


                        </div>


                    </div>


                </div>
            </div>
        </div>
    </main>
</template>



<script>

import LayoutApp from '../../../Layouts/App.vue';
import Pagination from '../../../Components/Pagination.vue';

import {
    Head,
    Link,
    router
} from '@inertiajs/vue3';

import { ref } from 'vue';
import { confirmDelete } from '@/Utils/confirmDialog';


export default {


    layout: LayoutApp,


    components:{
        Head,
        Link,
        Pagination
    },


    props:{
        categories:Object
    },


    setup(){

        const isLoading = ref(false);

        const handleDelete = async (id) => {


            if (await confirmDelete('Yakin ingin menghapus kategori ini?')) {

                isLoading.value = true;
                router.delete(
                    `/apps/categories/${id}`,
                    {
                        onFinish: () => { isLoading.value = false; },
                        onError: () => {
                            isLoading.value = false;
                        }
                    }
                );


            }


        };


        return {
            handleDelete,
            isLoading
        };


    }



};


</script>