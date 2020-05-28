import Study from "../../pages/Study";
import Train from "../../pages/Study/Train";
import Prepare from "../../pages/Study/Prepare";

export default [{
        path: '/study',
        label: 'Учить',
        icon: 'mdi-teach',
        component: Study,
        meta: {requiresAuth: true},
        menu: true,
        children: [
            {path:'',  name:'prepare', label: 'Настройка', icon: '', component: Prepare},
            {path:'/study/:id', name:'train', label: 'Начать', icon: '', component: Train, props: true}
        ],
}]