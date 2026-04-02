import { Routes, Route } from 'react-router-dom';
import Layout from './components/Layout';
import Home from './pages/Home';
import Contact from './pages/Contact';
import NewsDetail from './pages/NewsDetail';
import Login from './pages/admin/Login';
import AdminLayout from './components/AdminLayout';
import Categories from './pages/admin/Categories';
import CategoryForm from './pages/admin/CategoryForm';
import NewsList from './pages/admin/NewsList';
import NewsForm from './pages/admin/NewsForm';
import ContactsList from './pages/admin/ContactsList';
import MarketInstruments from './pages/admin/MarketInstruments';
import MarketInstrumentForm from './pages/admin/MarketInstrumentForm';

export default function AppRoutes() {
    return (
        <Routes>
            <Route element={<Layout />}>
                <Route path="/" element={<Home />} />
                <Route path="/contato" element={<Contact />} />
                <Route path="/noticias/:slug" element={<NewsDetail />} />
            </Route>

            <Route path="/admin/login" element={<Login />} />

            <Route path="/admin" element={<AdminLayout />}>
                <Route path="categorias" element={<Categories />} />
                <Route path="categorias/criar" element={<CategoryForm />} />
                <Route path="categorias/:id/editar" element={<CategoryForm />} />
                <Route path="noticias" element={<NewsList />} />
                <Route path="noticias/criar" element={<NewsForm />} />
                <Route path="noticias/:id/editar" element={<NewsForm />} />
                <Route path="contatos" element={<ContactsList />} />
                <Route path="ativos" element={<MarketInstruments />} />
                <Route path="ativos/criar" element={<MarketInstrumentForm />} />
                <Route path="ativos/:id/editar" element={<MarketInstrumentForm />} />
            </Route>
        </Routes>
    );
}
