import AppLayout from '@/layouts/app-layout'
import { 
  Card, 
  Container, 
  Group, 
  Stack, 
  Text, 
  Title,
  ThemeIcon,
  Badge,
  Button
} from '@mantine/core'
import { 
  FileText,
  Clock,
  Settings,
  Wrench
} from 'lucide-react'
import React from 'react'

const Logs = () => {
  return (
    <AppLayout active='logs'>
      <Container size="md" className="py-20">
        <Stack gap="xl" align="center">
          {/* Icon */}
          <ThemeIcon size={120} variant="light" color="blue" radius="xl">
            <FileText size={60} />
          </ThemeIcon>

          {/* Title */}
          <div className="text-center">
            <Title order={1} className="text-4xl font-bold text-gray-800 mb-4">
              Logs Système
            </Title>
            <Text size="xl" c="dimmed" className="max-w-lg mx-auto">
              Interface de consultation des journaux d'activité en cours de développement
            </Text>
          </div>

          {/* Status Badge */}
          <Badge size="lg" color="orange" variant="light" leftSection={<Clock size={16} />}>
            Fonctionnalité à venir
          </Badge>

          {/* Features Preview */}
          <Card shadow="sm" padding="xl" radius="md" withBorder className="max-w-md w-full">
            <Stack gap="md">
              <Text size="lg" fw={600} className="text-center">
                Fonctionnalités prévues
              </Text>
              
              <Stack gap="sm">
                <Group gap="sm">
                  <ThemeIcon size="sm" color="blue" variant="light">
                    <FileText size={14} />
                  </ThemeIcon>
                  <Text size="sm">Historique des synchronisations</Text>
                </Group>
                
                <Group gap="sm">
                  <ThemeIcon size="sm" color="green" variant="light">
                    <Settings size={14} />
                  </ThemeIcon>
                  <Text size="sm">Logs des accès Matrix</Text>
                </Group>
                
                <Group gap="sm">
                  <ThemeIcon size="sm" color="orange" variant="light">
                    <Wrench size={14} />
                  </ThemeIcon>
                  <Text size="sm">Journal des erreurs système</Text>
                </Group>
              </Stack>
            </Stack>
          </Card>

          {/* Action Button */}
          <Button 
            size="lg" 
            variant="light" 
            leftSection={<Clock size={20} />}
            disabled
          >
            Bientôt disponible
          </Button>
        </Stack>
      </Container>
    </AppLayout>
  )
}

export default Logs
