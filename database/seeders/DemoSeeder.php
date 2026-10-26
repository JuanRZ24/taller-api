<?php

namespace Database\Seeders;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Content;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

                $instructores = collect($this->instructores())->map(function (array $datos, int $i) {
            $user = User::factory()->create([
                'name' => $datos['name'],
                'email' => $datos['email'],
                'role' => UserRole::Instructor,
            ]);

            $perfil = $user->instructorProfile()->create([
                'headline' => $datos['headline'],
                'specialty' => $datos['specialty'],
                'biography' => $datos['biography'],
            ]);

            $perfil->photo_path = $this->fotoDe($datos['name'], $i);
            $perfil->save();

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
            $this->sembrarEstructura($curso, $this->estructuras()[$datos['title']] ?? []);
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

    private function sembrarEstructura(Course $curso, array $modulos): void
    {
        foreach ($modulos as $i => $mod) {
            $modulo = new Module(['title' => $mod['title']]);
            $modulo->position = $i + 1;
            $curso->modules()->save($modulo);

            foreach ($mod['contents'] as $j => $con) {
                $contenido = new Content([
                    'title' => $con['title'],
                    'type' => $con['type'],
                    'body' => $con['body'] ?? null,
                    'url' => $con['url'] ?? null,
                ]);
                $contenido->position = $j + 1;
                $modulo->contents()->save($contenido);
            }
        }
    }

        /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function estructuras(): array
    {
        return [
            'Laravel desde cero' => [
                ['title' => 'Antes de empezar', 'contents' => [
                    ['title' => 'Qué vas a construir', 'type' => 'text', 'body' => 'A lo largo del curso construimos una API completa: autenticación por tokens, validación en su capa, servicios con reglas de negocio y documentación generada desde el código.'],
                    ['title' => 'Instalación del entorno', 'type' => 'video', 'url' => 'https://www.youtube.com/watch?v=MYyJ4PuL4pY'],
                    ['title' => 'Documentación oficial', 'type' => 'link', 'url' => 'https://laravel.com/docs'],
                ]],
                ['title' => 'Rutas y controladores', 'contents' => [
                    ['title' => 'El archivo de rutas', 'type' => 'text', 'body' => 'Las rutas se evalúan en orden y la primera que coincide gana. Por eso una ruta con comodín puede atrapar a una ruta específica registrada después.'],
                    ['title' => 'Controladores en la práctica', 'type' => 'video', 'url' => 'https://www.youtube.com/watch?v=3hcwZFNvOx8'],
                ]],
                ['title' => 'Eloquent y relaciones', 'contents' => [
                    ['title' => 'Modelos y migraciones', 'type' => 'text', 'body' => 'La migración define la estructura y el modelo define el comportamiento. Las dos cosas tienen que contar la misma historia o el código miente.'],
                    ['title' => 'El problema N+1', 'type' => 'text', 'body' => 'Si listas veinte cursos y cada uno consulta su categoría al serializarse, son veintiún consultas. Con carga ansiosa son dos.'],
                    ['title' => 'Guía de relaciones', 'type' => 'link', 'url' => 'https://laravel.com/docs/eloquent-relationships'],
                ]],
                ['title' => 'Validación y autorización', 'contents' => [
                    ['title' => 'FormRequests', 'type' => 'text', 'body' => 'Validar en el controlador funciona hasta que la misma regla aparece en tres lugares. El FormRequest es donde esa regla vive una sola vez.'],
                    ['title' => 'Policies paso a paso', 'type' => 'video', 'url' => 'https://www.youtube.com/watch?v=TcLb2A5Z1gY'],
                ]],
            ],

            'APIs REST con PHP moderno' => [
                ['title' => 'Diseño de la API', 'contents' => [
                    ['title' => 'Sustantivos, no verbos', 'type' => 'text', 'body' => 'La ruta dice qué cosa y el método HTTP dice qué le haces. Si escribes el verbo en la URL, lo estás diciendo dos veces.'],
                    ['title' => 'Códigos de estado que importan', 'type' => 'text', 'body' => 'La diferencia entre 401 y 403 es la que más se confunde: el primero es identifícate, el segundo es ya te identificaste y aun así no.'],
                ]],
                ['title' => 'Versionado', 'contents' => [
                    ['title' => 'Por qué /v1 desde el día uno', 'type' => 'text', 'body' => 'Agregar un campo no rompe a nadie. Quitarlo o renombrarlo sí, y para entonces ya hay una app instalada en teléfonos que no vas a poder actualizar.'],
                    ['title' => 'Guía de versionado', 'type' => 'link', 'url' => 'https://restfulapi.net/versioning/'],
                ]],
                ['title' => 'Documentación', 'contents' => [
                    ['title' => 'Generada contra escrita a mano', 'type' => 'text', 'body' => 'La documentación escrita a mano duplica lo que ya dice el código, y el día que cambias uno y olvidas el otro, miente. La generada no puede desincronizarse.'],
                ]],
            ],

            'Seguridad en aplicaciones web' => [
                ['title' => 'Inyección', 'contents' => [
                    ['title' => 'Qué es la inyección SQL', 'type' => 'text', 'body' => 'Ocurre cuando entrada del usuario se concatena dentro de una consulta. El constructor de consultas la previene porque separa la instrucción de los datos.'],
                    ['title' => 'OWASP Top 10', 'type' => 'link', 'url' => 'https://owasp.org/www-project-top-ten/'],
                ]],
                ['title' => 'Asignación masiva', 'contents' => [
                    ['title' => 'El campo que nadie debería mandar', 'type' => 'text', 'body' => 'Si el rol viaja en el cuerpo de la petición, cualquiera se hace administrador con un curl. Lo que decide el servidor no viaja en el formulario.'],
                ]],
                ['title' => 'Sesiones y tokens', 'contents' => [
                    ['title' => 'Tokens opacos contra JWT', 'type' => 'text', 'body' => 'Un JWT lleva los datos escritos adentro y firmados: si cambias el rol en la base, el token viejo sigue diciendo lo de antes hasta que expire. Un token opaco apunta a un renglón y se lee fresco cada vez.'],
                    ['title' => 'Revocación en la práctica', 'type' => 'video', 'url' => 'https://www.youtube.com/watch?v=F-sFp_AvHc8'],
                ]],
            ],

            'Git para trabajo en equipo' => [
                ['title' => 'Ramas', 'contents' => [
                    ['title' => 'Cuándo merece rama propia', 'type' => 'text', 'body' => 'La prueba es simple: cuando la integres, ¿la rama de desarrollo puede hacer algo que antes no? Si la respuesta es no, agrúpala con lo que sigue.'],
                ]],
                ['title' => 'Conflictos', 'contents' => [
                    ['title' => 'Resolverlos sin miedo', 'type' => 'text', 'body' => 'Un conflicto no es un error: es Git diciéndote que dos personas tocaron lo mismo y que la decisión es humana, no automática.'],
                    ['title' => 'Guía de Git Flow', 'type' => 'link', 'url' => 'https://nvie.com/posts/a-successful-git-branching-model/'],
                ]],
            ],

            'Diseño de interfaces para desarrolladores' => [
                ['title' => 'Jerarquía visual', 'contents' => [
                    ['title' => 'Qué mira el ojo primero', 'type' => 'text', 'body' => 'El tamaño, el peso y el contraste deciden el orden de lectura. Si todo resalta, nada resalta.'],
                ]],
                ['title' => 'Espaciado', 'contents' => [
                    ['title' => 'El espacio también comunica', 'type' => 'text', 'body' => 'Los elementos relacionados van juntos y los que no, separados. La mitad de las interfaces confusas lo son por espaciado parejo, no por falta de color.'],
                ]],
            ],
        ];
    }


        /**
     * Genera una foto de perfil cuadrada con la inicial del instructor.
     * Se dibuja al vuelo para no meter imagenes binarias al repositorio.
     * No usa imagettftext porque el contenedor no trae fuentes .ttf.
     */
    private function fotoDe(string $nombre, int $indice): string
    {
        $paletas = [
            [45, 90, 160],
            [160, 80, 40],
            [40, 120, 110],
            [120, 60, 140],
            [160, 50, 70],
        ];

        [$r, $g, $b] = $paletas[$indice % count($paletas)];

        $lado = 28;
        $chico = imagecreatetruecolor($lado, $lado);
        imagefilledrectangle($chico, 0, 0, $lado, $lado, imagecolorallocate($chico, $r, $g, $b));

        $inicial = mb_strtoupper(mb_substr(trim($nombre), 0, 1));

        imagestring(
            $chico,
            5,
            intdiv($lado - imagefontwidth(5), 2),
            intdiv($lado - imagefontheight(5), 2),
            $inicial,
            imagecolorallocate($chico, 255, 255, 255),
        );

        $grande = imagescale($chico, 400, 400, IMG_NEAREST_NEIGHBOUR);

        ob_start();
        imagejpeg($grande, null, 90);
        $binario = ob_get_clean();

        imagedestroy($chico);
        imagedestroy($grande);

        $ruta = 'instructors/demo-'.Str::slug($nombre).'.jpg';
        Storage::disk('public')->put($ruta, $binario);

        return $ruta;
    }
}