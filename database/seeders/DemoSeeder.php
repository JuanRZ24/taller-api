<?php

namespace Database\Seeders;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $categorias = Category::pluck('id', 'name');

        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@demo.test',
            'role' => UserRole::Admin,
        ]);

        $instructores = collect($this->instructores())->map(function (array $datos) {
            $user = User::factory()->create([
                'name' => $datos['name'],
                'email' => $datos['email'],
                'role' => UserRole::Instructor,
            ]);

            $user->instructorProfile()->create([
                'headline' => $datos['headline'],
                'specialty' => $datos['specialty'],
                'biography' => $datos['biography'],
            ]);

            return $user;
        });

        User::factory(5)->create();

        foreach ($this->cursos() as $i => $datos) {
            $instructor = $instructores[$i % $instructores->count()];

            $curso = $instructor->courses()->create([
                'title' => $datos['title'],
                'description' => $datos['description'],
                'category_id' => $categorias[$datos['category']],
                'level' => $datos['level'],
            ]);

            $curso->status = $datos['status'];

            if ($datos['status'] === CourseStatus::Rejected) {
                $curso->rejection_reason = 'La descripción no detalla lo suficiente el contenido del curso.';
            }

            $curso->save();
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function instructores(): array
    {
        return [
            [
                'name' => 'Ana Herrera',
                'email' => 'ana@demo.test',
                'headline' => 'Ingeniera de software | 8 años enseñando backend',
                'specialty' => 'Desarrollo web',
                'biography' => 'Trabajo con PHP y Laravel desde 2017. He dado clases en línea a más de tres mil alumnos y me gusta explicar el porqué de las cosas, no solo el cómo.',
            ],
            [
                'name' => 'Diego Morales',
                'email' => 'diego@demo.test',
                'headline' => 'Desarrollador Android | Kotlin y Java',
                'specialty' => 'Desarrollo móvil',
                'biography' => 'Llevo seis años construyendo aplicaciones Android para empresas de logística. Enseño con proyectos reales, no con ejemplos de juguete.',
            ],
            [
                'name' => 'Laura Quintero',
                'email' => 'laura@demo.test',
                'headline' => 'Analista de datos | SQL, Python y visualización',
                'specialty' => 'Ciencia de datos',
                'biography' => 'Vengo de la estadística aplicada y aterricé en los datos hace ocho años. Mis cursos empiezan siempre por la pregunta que quieres responder, no por la herramienta.',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cursos(): array
    {
        return [
            ['title' => 'Laravel desde cero', 'category' => 'Desarrollo web', 'level' => CourseLevel::Beginner, 'status' => CourseStatus::Published, 'description' => 'Un recorrido completo por Laravel para quienes ya programan en otro lenguaje. Empezamos por el enrutamiento y terminamos construyendo una API con autenticación por tokens, validación y capa de servicios.'],
            ['title' => 'APIs REST con PHP moderno', 'category' => 'Desarrollo web', 'level' => CourseLevel::Intermediate, 'status' => CourseStatus::Published, 'description' => 'Diseño de APIs que no se rompen: versionado, códigos de estado, formatos de error uniformes y documentación generada desde el código. Incluye un proyecto completo de principio a fin.'],
            ['title' => 'JavaScript sin frameworks', 'category' => 'Desarrollo web', 'level' => CourseLevel::Beginner, 'status' => CourseStatus::Published, 'description' => 'Antes de aprender React conviene entender el navegador. Manipulación del DOM, eventos, peticiones asíncronas y almacenamiento local, con ejercicios cortos en cada tema.'],
            ['title' => 'Android nativo con Java', 'category' => 'Desarrollo móvil', 'level' => CourseLevel::Beginner, 'status' => CourseStatus::Published, 'description' => 'Construye tu primera aplicación Android desde el proyecto vacío: actividades, layouts, navegación entre pantallas y consumo de una API real con manejo de errores y estados de carga.'],
            ['title' => 'Kotlin para desarrolladores Java', 'category' => 'Desarrollo móvil', 'level' => CourseLevel::Intermediate, 'status' => CourseStatus::Published, 'description' => 'Si ya escribes Java, Kotlin se aprende en semanas. Nos enfocamos en lo que cambia de verdad: nulabilidad, funciones de extensión, corrutinas y las trampas al migrar código existente.'],
            ['title' => 'SQL para quienes nunca han consultado nada', 'category' => 'Bases de datos', 'level' => CourseLevel::Beginner, 'status' => CourseStatus::Published, 'description' => 'De un SELECT simple a consultas con varias tablas. Aprendes a leer un diagrama, a unir tablas sin perder registros por accidente y a entender por qué una consulta se vuelve lenta.'],
            ['title' => 'Modelado de bases de datos relacionales', 'category' => 'Bases de datos', 'level' => CourseLevel::Advanced, 'status' => CourseStatus::Published, 'description' => 'Normalización, llaves foráneas, índices y las decisiones que no se pueden deshacer después. Incluye casos reales donde un mal modelo costó meses de trabajo.'],
            ['title' => 'Python para análisis de datos', 'category' => 'Ciencia de datos', 'level' => CourseLevel::Beginner, 'status' => CourseStatus::Published, 'description' => 'Pandas de principio a fin, partiendo de archivos sucios de verdad: columnas con nombres inconsistentes, fechas en tres formatos y valores faltantes que significan cosas distintas.'],
            ['title' => 'Visualización que comunica', 'category' => 'Ciencia de datos', 'level' => CourseLevel::Intermediate, 'status' => CourseStatus::Published, 'description' => 'Una gráfica no es un adorno, es un argumento. Qué gráfica usar según la pregunta, cómo elegir colores accesibles y los errores que vuelven engañosa una visualización honesta.'],
            ['title' => 'Introducción a Docker', 'category' => 'Redes y seguridad', 'level' => CourseLevel::Beginner, 'status' => CourseStatus::Published, 'description' => 'Contenedores explicados sin misticismo: imágenes, volúmenes, redes y composición de servicios. Al terminar vas a poder levantar el entorno completo de un proyecto con un solo comando.'],
            ['title' => 'Git para trabajo en equipo', 'category' => 'Desarrollo web', 'level' => CourseLevel::Beginner, 'status' => CourseStatus::Pending, 'description' => 'Ramas, merge requests y resolución de conflictos sin miedo. Enfocado en el flujo que usan los equipos reales, incluyendo qué hacer cuando algo sale mal y hay que revertir.'],
            ['title' => 'Diseño de interfaces para desarrolladores', 'category' => 'Diseño', 'level' => CourseLevel::Beginner, 'status' => CourseStatus::Draft, 'description' => 'No necesitas ser diseñador para que tu aplicación no se vea mal. Jerarquía visual, espaciado, tipografía y color, explicados con reglas concretas que puedes aplicar de inmediato.'],
            ['title' => 'Seguridad en aplicaciones web', 'category' => 'Redes y seguridad', 'level' => CourseLevel::Advanced, 'status' => CourseStatus::Rejected, 'description' => 'Las vulnerabilidades más comunes y cómo se previenen desde el código: inyección, asignación masiva, XSS y manejo inseguro de sesiones. Con ejemplos de código vulnerable y su corrección.'],
        ];
    }
}