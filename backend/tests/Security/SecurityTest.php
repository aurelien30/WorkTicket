<?php

namespace App\Tests\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\UserPasswordHasherInterface;

class SecurityTest extends WebTestCase
{

    private function createTestUser(EntityManagerInterface $em,UserPasswordHasherInterface $hasher, array $roles = ['ROLE_USER']): User{
     
        $user = new User();
        $user->setEmail('test.user@workticket.local');
        $user->setNom('Test');
        $user->set6renom('User');
        $user->setRoles($roles);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setPassword($hasher->hashPassword($user, 'testpassword'));
    
        $em->persist($user);
        $em->flush();

        return $user;
        }

        public function testUnAuthenticatedUserIsRedirectedToLogin(): void{
            
            $client = static::createClient();
            $client->request('GET', '/tickets');
            
            // Sans être connecté, on est bloqué par une redirection ou une erreur401
            $this->assertResponseRedirects('/login');
        }

        public function testUserWithoutAdminRoleCannotAccessDashboard(): void{

            $client = static::createClient();
            $container = static::getContainer();

            $em = $container->get(EntityManagerInterface::class);
            $hasher = $container->get(UserPasswordHasherInterface::class);

            
            $user = $this->createTestUser($em, $hasher, ['ROLE_USER']);

            
            $client->loginUser($user);

            
            $client->request('GET', '/dashboard');

            
            $this->assertResponseStatusCodeSame(403);
        }
    }

