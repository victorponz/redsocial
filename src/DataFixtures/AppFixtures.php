<?php

namespace App\DataFixtures;

use App\Entity\Like;
use App\Entity\Tweet;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public const PLAIN_PASSWORD = '1234';
    private const NUM_TWEETS = 100;

    private const USERS = [
        ['username' => 'ana',    'fullName' => 'Ana García'],
        ['username' => 'luis',   'fullName' => 'Luis Martínez'],
        ['username' => 'marta',  'fullName' => 'Marta López'],
        ['username' => 'pablo',  'fullName' => 'Pablo Sánchez'],
        ['username' => 'lucia',  'fullName' => 'Lucía Fernández'],
        ['username' => 'javier', 'fullName' => 'Javier Gómez'],
        ['username' => 'elena',  'fullName' => 'Elena Ruiz'],
        ['username' => 'carlos', 'fullName' => 'Carlos Díaz'],
        ['username' => 'sara',   'fullName' => 'Sara Moreno'],
        ['username' => 'david',  'fullName' => 'David Romero'],
    ];

    private const PHRASES = [
        'Hoy he aprendido algo nuevo sobre Symfony',
        'Qué buen día hace para programar',
        'Acabo de terminar mi primera migración con Doctrine',
        'Alguien recomienda un buen libro de PHP',
        'Café y código, la mejor combinación',
        'Por fin he entendido las expresiones regulares',
        'Las plantillas Twig son muy cómodas',
        'Mañana examen, deseadme suerte',
        'Probando la nueva red social de clase',
        'El patrón MVC tiene todo el sentido del mundo',
        'Hoy toca repasar SQL y relaciones ManyToMany',
        'Me encanta cuando los tests pasan a la primera',
        'Viernes de despliegue, qué puede salir mal',
        'Acabo de descubrir los fixtures, qué maravilla',
        'Git salva vidas, haced commits a menudo',
    ];

    private const HASHTAGS = ['php', 'symfony', 'doctrine', 'twig', 'daw', 'programacion', 'sql', 'git'];

    private const LINKS = ['https://symfony.com', 'https://www.php.net', 'https://twig.symfony.com'];

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        mt_srand(2026); // datos reproducibles

        // Usuarios
        $users = [];
        foreach (self::USERS as $data) {
            $user = new User();
            $user->setUsername($data['username'])
                ->setFullName($data['fullName'])
                ->setEmail($data['username'] . '@example.com') // la columna admite 25 caracteres
                ->setAvatar('')
                ->setRoles([])
                ->setPassword($this->passwordHasher->hashPassword($user, self::PLAIN_PASSWORD));
            $manager->persist($user);
            $users[] = $user;
        }
        $users[0]->setRoles(['ROLE_ADMIN']);

        // Seguidores: cada usuario sigue a entre 2 y 6 usuarios distintos de sí mismo
        foreach ($users as $user) {
            $others = array_values(array_filter($users, fn (User $u) => $u !== $user));
            shuffle($others);
            foreach (array_slice($others, 0, mt_rand(2, 6)) as $followed) {
                $user->addFollowing($followed);
                $followed->addFollower($user);
            }
        }

        // Mensajes, repartidos en los últimos 30 días
        $tweets = [];
        for ($i = 0; $i < self::NUM_TWEETS; $i++) {
            $author = $users[mt_rand(0, count($users) - 1)];

            $tweet = new Tweet();
            $tweet->setContent($this->randomContent($author, $users))
                ->setLikes(0)
                ->setDate(new \DateTime(sprintf('-%d minutes', mt_rand(0, 30 * 24 * 60))));
            $author->addTweet($tweet);
            $manager->persist($tweet);
            $tweets[] = $tweet;
        }

        // Likes: cada mensaje recibe entre 0 y 6 likes de usuarios distintos (nunca del autor)
        foreach ($tweets as $tweet) {
            $candidates = array_values(array_filter($users, fn (User $u) => $u !== $tweet->getUser()));
            shuffle($candidates);
            foreach (array_slice($candidates, 0, mt_rand(0, 6)) as $liker) {
                $like = new Like();
                $like->setDate($this->randomDateAfter($tweet->getDate()));
                $tweet->addLikesEntity($like);
                $liker->addLike($like);
                $tweet->addLike(); // mantiene sincronizado el contador
                $manager->persist($like);
            }
        }

        $manager->flush();
    }

    /** @param User[] $users */
    private function randomContent(User $author, array $users): string
    {
        $content = self::PHRASES[mt_rand(0, count(self::PHRASES) - 1)];

        if (mt_rand(1, 100) <= 30) {
            $mentioned = $users[mt_rand(0, count($users) - 1)];
            if ($mentioned !== $author) {
                $content = '@' . $mentioned->getUsername() . ' ' . $content;
            }
        }
        if (mt_rand(1, 100) <= 60) {
            $content .= ' #' . self::HASHTAGS[mt_rand(0, count(self::HASHTAGS) - 1)];
        }
        if (mt_rand(1, 100) <= 15) {
            $content .= ' ' . self::LINKS[mt_rand(0, count(self::LINKS) - 1)];
        }

        return $content;
    }

    private function randomDateAfter(\DateTimeInterface $date): \DateTime
    {
        $now = time();
        $start = $date->getTimestamp();

        return (new \DateTime())->setTimestamp(mt_rand($start, max($start, $now)));
    }
}
