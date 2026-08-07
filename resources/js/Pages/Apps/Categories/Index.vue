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
                                        <th>Nama</th>
                                        <th>Deskripsi</th>
                                        <th>Jumlah Item</th>
                                        <th width="15%" class="text-center">
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
                                                :href="`/apps/categories/${cat.id}/edit`"
                                                class="btn btn-success btn-sm me-2"
                                            >
                                                <i class="fa fa-pencil-alt"></i>
                                            </Link>



                                            <button
                                                class="btn btn-danger btn-sm"
                                                @click="handleDelete(cat.id)"
                                            >

                                                <i class="fa fa-trash"></i>

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


        const handleDelete=(id)=>{


            if(confirm('Yakin ingin menghapus kategori ini?')){


                router.delete(
                    `/apps/categories/${id}`
                );


            }


        };


        return {
            handleDelete
        };


    }



};


</script>