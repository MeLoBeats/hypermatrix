import { Box, Group, Text, ThemeIcon, Badge } from "@mantine/core";
import { Key, Activity } from "lucide-react";
import AccountMenu from "./account-menu";

const AppHeader = () => {
    return (
        <Box h={"100%"} px="md">
            <Group align="center" h={"100%"} justify="space-between">
                <Group gap="md" align="center">
                    <ThemeIcon size={40} variant="gradient" gradient={{ from: 'deepBlue.9', to: 'deepBlue.6' }}>
                        <Key size={20} />
                    </ThemeIcon>
                    <div>
                        <Text size="xl" fw={700} c="white">
                            Hypermatrix
                        </Text>
                        <Text size="xs" c="white" opacity={0.8}>
                            Supervision des accès
                        </Text>
                    </div>
                </Group>
                
                <Group gap="md" align="center">
                    <Badge 
                        size="sm" 
                        color="green" 
                        variant="light"
                        leftSection={<Activity size={12} />}
                    >
                        Système opérationnel
                    </Badge>
                    <AccountMenu />
                </Group>
            </Group>
        </Box>
    );
};

export default AppHeader;
