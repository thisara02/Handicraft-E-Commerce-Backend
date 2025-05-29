# 🌿 Serendib Galleria

> **"Empowering tradition through technology — where heritage meets the global marketplace."**

Serendib Galleria is a culturally focused e-commerce platform that enables Sri Lankan artisans and SMEs to sell their handcrafted products directly to customers around the world. The system promotes fair trade, reduces dependence on middlemen, and supports the digital inclusion of local craft vendors.

---
## 🔗 Project Links

- 🔸 **Frontend GitHub Repo:** [Handicraft E-Commerce Frontend](https://github.com/thisara02/Handicraft-E-Commerce)
- 🔸 **Backend GitHub Repo:** [Handicraft E-Commerce Backend](https://github.com/thisara02/Handicraft-E-Commerce-Backend)
- 🎥 **Figma UI/UX Design:** [Visit the UI](https://www.figma.com/design/c3JMZQMh0d8zsCcQmHITIj/Handicraft-ECommerce?node-id=0-1&t=3rzneCKJXsiVJrwN-1)

---

## ⚙️ Installation Guide (Localhost Setup)

This guide explains how to run the application locally using Laravel for the backend and React.js for the frontend.

---

### 🖥️ Backend Setup (Laravel + MySQL)

1. **Clone the Backend Repository**
   ```bash
   git clone https://github.com/thisara02/Handicraft-E-Commerce-Backend.git
   ```

2. **Navigate to the project directory**
   ```bash
   cd handicraft-backend
   ```

3. **Install Composer dependencies**
   ```bash
   composer install
   ```

4. **Copy `.env` file**
   ```bash
   cp .env.example .env
   ```

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Create a new MySQL database**
   - Use MySQL Workbench or phpMyAdmin
   - **Database name:** `handicraft`

7. **Configure `.env` file with DB credentials**
   ```
   DB_DATABASE=handicraft
   DB_USERNAME=your_db_username
   DB_PASSWORD=your_db_password
   ```

8. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

9. **Start Laravel development server**
   ```bash
   php artisan serve
   ```

10. **Backend runs at:**  
    `http://127.0.0.1:8000`

---

### 💻 Frontend Setup (React.js + Vite)

1. **Clone the Frontend Repository**
   ```bash
   git clone https://github.com/thisara02/Handicraft-E-Commerce.git
   ```

2. **Navigate to the frontend directory**
   ```bash
   cd Handicraft-E-Commerce/client
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Create `.env` file with backend URL**
   ```
   VITE_API_BASE_URL=http://127.0.0.1:8000/api
   ```

5. **Start the development server**
   ```bash
   npm start
   ```

6. **Frontend runs at:**  
   `http://localhost:3000`

---

## 📸 Features at a Glance

- ✅ Vendor registration & product listing
- ✅ Admin approval workflow
- ✅ Event and offer management
- ✅ Cart, filtering, and search functionality
- ✅ Stripe-integrated secure checkout
- ✅ JWT authentication & role-based access
- ✅ Mobile-responsive, modern UI

---

## 👨‍💻 Developer

- **Name:** [Your Name]  
- **Role:** Final Year Software Engineering Student  
- **Supervisor:** Mr. Gayan Rukshantha Perera

---

## 📜 License

This project is for academic purposes and is open to enhancement under the MIT License.

---

> 💬 *“Support local culture, empower artisans, and bring Sri Lankan heritage to the world.”*
