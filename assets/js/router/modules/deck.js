import DeckList from "../../pages/Decks/DeckList";
import Decks from "../../pages/Decks"
import DeckDetail from "../../pages/Decks/DeckDetail";

export default [{
        path:'/decks',
        label: 'Колоды',
        icon: 'mdi-folder',
        component:Decks,
        meta: { requiresAuth: true },
        menu: true,
        children: [
            {path:'',           name:'decks',       label: 'Колоды',    icon: 'mdi-folder', component: DeckList},
            {path:':id/',       name:'deck-get',    label: 'Колода',    icon: '', component: DeckDetail,  props: true},
        ]
}]